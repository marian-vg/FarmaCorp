<?php

namespace App\Livewire\User;

use App\Models\Product;
use App\Models\Caja;
use App\Models\MovimientoCaja;
use App\Models\MedioPago;
use App\Models\Factura;
use App\Models\Client;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Flux\Flux;

class VentaManager extends Component
{
    use WithPagination;

    public $carrito = [];
    public $search = '';
    public $search_cliente = '';
    public $medio_pago_id = '';
    public $tipo_comprobante = '';
    public $cliente_id = null;
    public $tabActiva = 'vender';
    public $es_cuenta_corriente = false;
    public $filtroEstado = '';
    public $facturaSeleccionada = null;
    public $global_adjustment = 0;
    public $pagos_realizados = [];
    public $monto_pago_actual = 0;
    public ?\App\Models\Medicine $viewingMedicine = null;
    public $ultimaFacturaId = null;

    public function viewLeaflet($productId)
    {
        $this->viewingMedicine = \App\Models\Medicine::with('product')->where('product_id', $productId)->first();

        if ($this->viewingMedicine) {
            Flux::modal('leaflet-modal')->show();
        } else {
            $this->dispatch('notify', message: 'Este producto no tiene prospecto clínico registrado.', variant: 'warning');
        }
    }

    public function descargarFactura($id)
    {
        // Cargamos la factura con todas sus relaciones para el reporte
        $factura = Factura::with(['user', 'cliente', 'details.product', 'pagos.medioPago'])->findOrFail($id);

        // Seguridad Básica: Solo admin o el usuario que la emitió
        if (!Auth::user()->hasRole('admin') && $factura->user_id !== Auth::id()) {
            $this->dispatch('notify', message: 'No tiene permisos para descargar este comprobante.', variant: 'danger');
            return;
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.factura', [
            'factura' => $factura,
        ]);

        return response()->streamDownload(
            fn () => print($pdf->output()),
            "Comprobante-{$factura->tipo_comprobante}-#{$factura->id}.pdf"
        );
    }

    public function updatedGlobalAdjustment()
    {
        $totalPagado = collect($this->pagos_realizados)->sum('monto');
        
        // Si al poner el descuento, lo que ya pagaron supera el nuevo total...
        if ($totalPagado > $this->totalFinal) {
            $this->pagos_realizados = []; // Limpiamos los pagos por seguridad
            $this->dispatch('notify', 
                message: 'El total ha cambiado y supera los pagos registrados. Por favor, cargue los medios de pago nuevamente.', 
                variant: 'warning'
            );
        }
    }

    public function autocompletarMonto()
    {
        $this->monto_pago_actual = round($this->montoRestante, 2);
    }

    #[Computed]
    public function montoRestante()
    {
        $totalVenta = (float) $this->totalFinal;
        $totalPagado = collect($this->pagos_realizados)->sum(fn($p) => (float) $p['monto']);
        
        $resultado = $totalVenta - $totalPagado;

        return round($resultado, 2) > 0 ? round($resultado, 2) : 0;
    }

    public function agregarPago()
    {
        $this->validate([
            'medio_pago_id' => 'required|exists:medio_pagos,id',
            'monto_pago_actual' => 'required|numeric|min:0.01',
        ]);

        if ((float)$this->monto_pago_actual > ($this->montoRestante + 0.01)) {
            $this->dispatch('notify', message: 'El monto supera el saldo restante.', variant: 'warning');
            return;
        }

        $medio = \App\Models\MedioPago::find($this->medio_pago_id);

        $this->pagos_realizados[] = [
            'medio_id' => $this->medio_pago_id,
            'monto' => (float) $this->monto_pago_actual,
            'nombre' => $medio->nombre,
        ];

        $this->reset(['medio_pago_id', 'monto_pago_actual']);
        unset($this->montoRestante); 
    }

    public function quitarPago($index)
    {
        unset($this->pagos_realizados[$index]);
        $this->pagos_realizados = array_values($this->pagos_realizados);
    }

    #[Computed]
    public function clientes()
    {
        return \App\Models\Client::search($this->search_cliente)
            ->take(5)
            ->get();
    }

    public function verDetalle($facturaId)
    {
        $this->facturaSeleccionada = null; 
        $this->facturaSeleccionada = Factura::with(['details.product', 'pagos.medioPago'])->findOrFail($facturaId);
        \Flux::modal('detalle-venta-modal')->show();
    }

    #[Computed]
    public function totalFinal()
    {
        $sumaProductos = collect($this->carrito)->sum(fn($item) => $item['price'] * $item['cantidad']);
        return floatval($sumaProductos) + floatval($this->global_adjustment);
    }

    #[Computed]
    public function cajaActiva()
    {
        return Caja::where('user_id', Auth::id())->whereNull('fecha_cierre')->first();
    }

    public function agregarAlCarrito(Product $product)
    {

        $maxDays = (int) (\App\Models\Setting::where('key', 'price_max_days')->first()?->value ?? 30);

        if ($product->price_expires_at && $product->price_expires_at->isPast()) {
            $this->dispatch('notify', 
                message: "BLOQUEO: El precio de {$product->name} caducó el " . $product->price_expires_at->format('d/m/Y') . ". Actualícelo en el catálogo.", 
                variant: 'danger'
            );
            return;
        }

        $ultimoCambio = $product->price_updated_at ?: $product->created_at;
        $diasAntiguedad = $ultimoCambio->diffInDays(now());

        if ($diasAntiguedad > $maxDays) {
            $this->dispatch('notify', 
                message: "ALERTA: El precio de {$product->name} tiene {$diasAntiguedad} días de antigüedad (Límite: {$maxDays}). Debe ser actualizado para poder facturarse.", 
                variant: 'danger'
            );
            return;
        }

        if (!$this->tipo_comprobante) {
            $this->dispatch('notify', message: 'Primero selecciona un tipo de comprobante.', variant: 'warning');
            return;
        }

        $stockDisponible = $product->stock?->cantidad_actual ?? 0;
        $cantidadEnCarrito = isset($this->carrito[$product->id]) ? $this->carrito[$product->id]['cantidad'] : 0;

        if ($stockDisponible <= $cantidadEnCarrito) {
            $this->dispatch('notify', message: 'No hay más stock disponible.', variant: 'danger');
            return;
        }

        if (isset($this->carrito[$product->id])) {
            $this->carrito[$product->id]['cantidad']++;
        } else {
            $this->carrito[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'cantidad' => 1,
            ];
        }
        $this->dispatch('notify', message: "Añadido: {$product->name}");
    }

    public function quitarDelCarrito($productId)
    {
        unset($this->carrito[$productId]);
    }

    #[Computed]
    public function subtotal()
    {
        return collect($this->carrito)->sum(fn($item) => $item['price'] * $item['cantidad']);
    }

    public function procesarVenta()
    {
        // 1. Validaciones Iniciales (Caja, Carrito, Totales)
        if (!$this->cajaActiva) {
            $this->dispatch('notify', message: 'Error: Debes abrir caja antes de vender.', variant: 'danger');
            return;
        }

        if (empty($this->carrito)) {
            $this->dispatch('notify', message: 'El carrito está vacío.', variant: 'warning');
            return;
        }

        $totalPagado = collect($this->pagos_realizados)->sum('monto');
        $montoVenta = round((float)$this->totalFinal, 2);
        $pagadoReal = round((float)$totalPagado, 2);

        if ($pagadoReal > $montoVenta) {
            $this->dispatch('notify', 
                message: 'Error: El monto pagado ($' . number_format($pagadoReal, 2) . ') supera el total. Ajuste los pagos.', 
                variant: 'danger'
            );
            return;
        }

        if ($pagadoReal < $montoVenta && !$this->cliente_id) {
            $this->dispatch('notify', message: 'Falta cubrir $' . number_format($montoVenta - $pagadoReal, 2) . '. Selecciona un cliente para dejar saldo pendiente.', variant: 'warning');
            return;
        }

        if ($montoVenta < 0) {
            $this->dispatch('notify', message: 'El descuento no puede superar el monto total.', variant: 'danger');
            return;
        }

        $this->validate(['tipo_comprobante' => 'required']);

        // 2. Transacción de Base de Datos
        $facturaID = \DB::transaction(function () use ($montoVenta, $pagadoReal) {
            $estadoFactura = ($pagadoReal >= $montoVenta) ? 'PAGADO' : 'PENDIENTE';

            $factura = Factura::create([
                'tipo_comprobante' => $this->tipo_comprobante,
                'fecha_emision'    => now(),
                'total'            => $this->totalFinal,
                'ajuste_global'    => $this->global_adjustment,
                'estado'           => $estadoFactura, 
                'user_id'          => Auth::id(),
                'cliente_id'       => $this->cliente_id,
                'medio_pago_id'    => null, 
            ]);

            foreach ($this->carrito as $item) {
                \App\Models\FacturaDetalle::create([
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $item['price'],
                    'descuento'       => 0,
                    'factura_id'      => $factura->id,
                    'product_id'      => $item['id'],
                ]);

                $stockGlobal = \App\Models\Stock::where('product_id', $item['id'])->first();
                if ($stockGlobal) {
                    $stockGlobal->cantidad_actual -= $item['cantidad'];
                    $stockGlobal->save();
                }

                $cantidadRestante = $item['cantidad'];
                $lotes = \App\Models\Batch::where('medicine_id', $item['id'])
                    ->where('current_quantity', '>', 0)
                    ->orderBy('expiration_date', 'asc')
                    ->get();

                foreach ($lotes as $lote) {
                    if ($cantidadRestante <= 0) break;
                    $aQuitar = min($lote->current_quantity, $cantidadRestante);
                    $lote->current_quantity -= $aQuitar;
                    $lote->save();

                    \App\Models\StockMovement::create([
                        'batch_id' => $lote->id,
                        'user_id'  => Auth::id(),
                        'type'     => 'egreso',
                        'reason'   => 'venta',
                        'quantity' => $aQuitar
                    ]);
                    $cantidadRestante -= $aQuitar;
                }
            }

            foreach ($this->pagos_realizados as $pago) {
                MovimientoCaja::create([
                    'tipo_movimiento'  => 'INGRESO',
                    'monto'            => $pago['monto'],
                    'motivo'           => "Venta #{$factura->id} - Pago parcial: {$pago['nombre']}",
                    'fecha_movimiento' => now(),
                    'id_medio_pago'    => $pago['medio_id'],
                    'id_caja'          => $this->cajaActiva->id,
                    'user_id'          => Auth::id(),
                    'factura_id'       => $factura->id,
                ]);
            }

            return $factura->id;
        });

        $this->ultimaFacturaId = $facturaID;

        // 3. Lógica del RF-20: Comportamiento de Cierre
        $action = \App\Models\Setting::where('key', 'post_sale_action')->first()?->value ?? 'preguntar';

        if ($action === 'auto_imprimir') {
            $this->limpiarVenta();
            // En lugar de return, disparamos un evento de JavaScript para abrir en pestaña nueva
            $url = route('factura.imprimir', ['id' => $facturaID]);
            $this->dispatch('abrir-impresion', url: $url);
            return;
        }
        
        if ($action === 'preguntar') {
            // Mostramos el modal para que el usuario elija
            $this->limpiarVenta();
            Flux::modal('exito-venta-modal')->show();
        } else {
            // 'solo_guardar': Solo notificamos y limpiamos
            $this->limpiarVenta();
            $this->dispatch('notify', message: 'Operación registrada con éxito.');
        }
    }

    public function generarPdfStream($id)
    {
        $factura = Factura::with(['user', 'cliente', 'details.product', 'pagos.medioPago'])->findOrFail($id);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.factura', [
            'factura' => $factura,
        ]);

        // CAMBIO: Usamos stream() en lugar de inline()
        // Esto envía el PDF al navegador para que se visualice
        return $pdf->stream("Factura-#{$factura->id}.pdf");
    }

    // Método auxiliar para no repetir código de limpieza
    private function limpiarVenta()
    {
        $this->reset([
            'carrito', 'pagos_realizados', 'tipo_comprobante', 
            'cliente_id', 'search_cliente', 'global_adjustment', 
            'monto_pago_actual', 'medio_pago_id'
        ]);

        unset($this->totalFinal, $this->montoRestante, $this->subtotal);
    }

    #[Computed]
    public function historialVentas()
    {
        return Factura::query()
            // CARGA ANSIOSA: Agregamos 'cliente' a la lista
            ->with(['user', 'pagos.medioPago', 'cliente']) 
            ->when(!Auth::user()->hasRole('admin'), function($q) {
                $q->where('user_id', Auth::id());
            })
            ->when($this->filtroEstado, function($q) {
                $q->where('estado', $this->filtroEstado);
            })
            ->orderBy('fecha_emision', 'desc')
            ->paginate(10);
    }

    public function render()
    {
        // Obtenemos la configuración una sola vez para la vista (RF-21)
        $maxDays = (int) (\App\Models\Setting::where('key', 'price_max_days')->first()?->value ?? 30);

        $products = Product::query()
            ->leftJoin('stocks', 'products.id', '=', 'stocks.product_id')
            ->where('products.status', true)
            ->where('products.name', 'like', "%{$this->search}%")
            ->orderByRaw('CASE WHEN stocks.cantidad_actual > 0 THEN 0 ELSE 1 END ASC')
            ->orderBy('products.name', 'asc')
            ->select('products.*')
            ->with(['stock', 'medicine']) 
            ->get();

        return view('livewire.user.venta-manager', [
            'products' => $products,
            'mediosPago' => MedioPago::all(),
            'maxDays' => $maxDays // Enviamos el límite a la vista
        ])->layout('components.layouts.app');
    }
}