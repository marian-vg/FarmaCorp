<?php

namespace App\Livewire\User;

use App\Events\StockActualizado;
use App\Models\Batch;
use App\Models\Caja;
use App\Models\Client;
use App\Models\Factura;
use App\Models\FacturaDetalle;
use App\Models\Group;
use App\Models\Medicine;
use App\Models\MedioPago;
use App\Models\MovimientoCaja;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Setting;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Traits\Notifies;
use App\Models\Prescription;
use Barryvdh\DomPDF\Facade\Pdf;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app', ['title' => 'Venta'])]
class VentaManager extends Component
{
    use Notifies, WithPagination, WithFileUploads;

    public $receta_pdf;

    /**
     * Refreshes medicine availability when stock changes are recorded by admins.
     */
    #[On('echo:stock-channel,.stock.actualizado')]
    public function refrescarMedicamentos(): void
    {
        Log::info('[VentaManager] Websocket trigger recibido (stock.actualizado). Forzando refresco de catálogo...');

        unset($this->medicines);
    }

    /**
     * Returns the correct SQL LIKE operator for the current database driver.
     * PostgreSQL uses ILIKE for case-insensitive matching; SQLite uses LIKE.
     */
    private function likeOperator(): string
    {
        return \DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }

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

    public ?Medicine $viewingMedicine = null;

    public $ultimaFacturaId = null;

    public $promotion_id = null;

    public $filterGroup = '';

    public $fecha_desde = '';

    public $fecha_hasta = '';

    // Fase 11: Cantidades Personalizadas de Carrito
    public $customQuantity = 1;

    public $customMedicineId = null;

    public $customOperation = 'agregar'; // 'agregar' o 'quitar'

    // --- PROPIEDADES DEL SIMULADOR DE OBRA SOCIAL ---
    public $showValidationModal = false;
    public $doctor_license = ''; // Matrícula
    public $prescription_date = ''; // Fecha de la receta
    public $is_validated = false; // ¿Pasó la validación?
    public $authorization_code = ''; // El AUTH-XXXXX
    public $os_discount_amount = 0; // Monto total ahorrado por OS

    public function viewLeaflet($productId)
    {
        $this->viewingMedicine = Medicine::with('product')->where('product_id', $productId)->first();

        if ($this->viewingMedicine) {
            Flux::modal('leaflet-modal')->show();
        } else {
            $this->notify('Este producto no tiene prospecto clínico registrado.', 'warning');
        }
    }

    public function descargarFactura($id)
    {
        // Cargamos la factura con todas sus relaciones para el reporte
        $factura = Factura::with(['user', 'cliente', 'details.product', 'pagos.medioPago'])->findOrFail($id);

        if (! Auth::user()->hasRole('admin') && $factura->user_id !== Auth::id()) {
            $this->notify('No tiene permisos para descargar este comprobante.', 'error');

            return;
        }

        $pdf = Pdf::loadView('pdf.factura', [
            'factura' => $factura,
        ]);

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            "Comprobante-{$factura->tipo_comprobante}-#{$factura->id}.pdf"
        );
    }

    public function updatedGlobalAdjustment()
    {
        $totalPagado = collect($this->pagos_realizados)->sum('monto');

        if ($totalPagado > $this->totalFinal) {
            $this->pagos_realizados = []; // Limpiamos los pagos por seguridad
            $this->notify('El total ha cambiado y supera los pagos registrados. Por favor, cargue los medios de pago nuevamente.', 'warning');
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
        $totalPagado = collect($this->pagos_realizados)->sum(fn ($p) => (float) $p['monto']);

        $resultado = $totalVenta - $totalPagado;

        return round($resultado, 2) > 0 ? round($resultado, 2) : 0;
    }

    public function agregarPago()
    {
        $this->validate([
            'medio_pago_id' => 'required|exists:medio_pagos,id',
            'monto_pago_actual' => 'required|numeric|min:0.01',
        ]);

        if ((float) $this->monto_pago_actual > ($this->montoRestante + 0.01)) {
            $this->notify('El monto supera el saldo restante.', 'warning');

            return;
        }

        $medio = MedioPago::find($this->medio_pago_id);

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
        return Client::search($this->search_cliente)
            ->take(5)
            ->get();
    }

    public function verDetalle($facturaId)
    {
        $this->facturaSeleccionada = null;
        $this->facturaSeleccionada = Factura::with(['details.product', 'pagos.medioPago'])->findOrFail($facturaId);
        Flux::modal('detalle-venta-modal')->show();
    }

    #[Computed]
    public function totalFinal()
    {
        $total = ($this->subtotal + $this->global_adjustment) - $this->os_discount_amount;
        return round($total, 2);
    }

    #[Computed]
    public function cajaActiva()
    {
        return Caja::where('user_id', Auth::id())->whereNull('fecha_cierre')->first();
    }

    public function agregarAlCarrito(Medicine $medicine)
    {
        $maxDays = (int) (Setting::where('key', 'price_max_days')->first()?->value ?? 30);

        // Ya que el precio está en la presentación en el nuevo Vademécum
        // Por seguridad fall-back a price_expires_at del product (como estaba antes), o se podría migrar tmb
        $product = $medicine->product;

        if ($product->price_expires_at && $product->price_expires_at->isPast()) {
            $this->notify("BLOQUEO: El precio de {$product->name} caducó el ".$product->price_expires_at->format('d/m/Y').'. Actualícelo en el catálogo.', 'error');

            return;
        }

        $ultimoCambio = $product->price_updated_at ?: $product->created_at;
        $diasAntiguedad = $ultimoCambio->diffInDays(now());

        if ($diasAntiguedad > $maxDays) {
            $this->notify("ALERTA: El precio de {$product->name} tiene {$diasAntiguedad} días de antigüedad (Límite: {$maxDays}). Debe ser actualizado para poder facturarse.", 'error');

            return;
        }

        if (! $this->tipo_comprobante) {
            $this->notify('Primero selecciona un tipo de comprobante.', 'error');

            return;
        }

        $stockDisponible = $medicine->stock?->cantidad_actual ?? 0;
        $cantidadEnCarrito = isset($this->carrito[$medicine->id]) ? $this->carrito[$medicine->id]['cantidad'] : 0;

        if ($stockDisponible <= $cantidadEnCarrito) {
            $this->notify('No hay más stock disponible.', 'error');

            return;
        }

        if (isset($this->carrito[$medicine->id])) {
            $this->carrito[$medicine->id]['cantidad']++;
        } else {
            $this->carrito[$medicine->id] = [
                'id' => $medicine->id,
                'product_id' => $product->id,
                'name' => $medicine->presentation_name ?: $product->name,
                'price' => $medicine->price,
                'cantidad' => 1,
                'requires_prescription' => $medicine->requires_prescription,
            ];
        }
        $this->notify('Añadido: '.($medicine->presentation_name ?: $product->name), 'success');
        $this->recalculateOSDiscount();
    }

    public function quitarUnoDelCarrito($medicineId)
    {
        if (isset($this->carrito[$medicineId])) {
            $this->carrito[$medicineId]['cantidad']--;
            if ($this->carrito[$medicineId]['cantidad'] <= 0) {
                unset($this->carrito[$medicineId]);
            }
        }
        $this->recalculateOSDiscount();
    }

    public function openCustomModal($medicineId, $operation = 'agregar')
    {
        $this->customMedicineId = $medicineId;
        $this->customOperation = $operation;
        $this->customQuantity = 1;
        Flux::modal('custom-quantity-modal')->show();
    }

    public function processCustomQuantity()
    {
        $this->validate([
            'customQuantity' => 'required|integer|min:1',
        ]);

        $medicine = Medicine::with('product', 'stock')->find($this->customMedicineId);

        if (! $medicine) {
            return;
        } // Salvaguardia

        $cantidadActual = isset($this->carrito[$medicine->id]) ? $this->carrito[$medicine->id]['cantidad'] : 0;
        $stockDisponible = $medicine->stock?->cantidad_actual ?? 0;

        if ($this->customOperation === 'agregar') {
            if (($cantidadActual + $this->customQuantity) > $stockDisponible) {
                $this->notify('Error: Deseas añadir '.$this->customQuantity.', pero el inventario solo admite '.($stockDisponible - $cantidadActual).' unidades más de este lote físico.', 'error');
                Flux::modal('custom-quantity-modal')->close();

                return;
            }

            if (isset($this->carrito[$medicine->id])) {
                $this->carrito[$medicine->id]['cantidad'] += $this->customQuantity;
            } else {
                $this->carrito[$medicine->id] = [
                    'id' => $medicine->id,
                    'product_id' => $medicine->product->id,
                    'name' => $medicine->presentation_name ?: $medicine->product->name,
                    'price' => $medicine->price,
                    'cantidad' => $this->customQuantity,
                    'requires_prescription' => $medicine->requires_prescription,
                ];
            }
            $this->notify('Se añadieron '.$this->customQuantity.' unidades correctamente.', 'success');

        } elseif ($this->customOperation === 'quitar') {
            if ($this->customQuantity >= $cantidadActual) {
                unset($this->carrito[$medicine->id]);
            } else {
                $this->carrito[$medicine->id]['cantidad'] -= $this->customQuantity;
            }
            $this->notify('Se quitaron unidades correctamente.', 'success');
        }

        Flux::modal('custom-quantity-modal')->close();
        $this->reset(['customQuantity', 'customMedicineId', 'customOperation']);
    }

    public function quitarDelCarrito($medicineId)
    {
        unset($this->carrito[$medicineId]);
        $this->recalculateOSDiscount();
    }

    public function validarConObraSocial()
    {
        $this->validate([
            'doctor_license' => 'required|string|min:4',
            'prescription_date' => 'required|date|before_or_equal:today',
        ]);

        // 1. Verificar antigüedad de la receta (Regla de los 30 días)
        $fecha = \Carbon\Carbon::parse($this->prescription_date);
        if ($fecha->diffInDays(now()) > 30) {
            $this->notify('Validación Fallida: La receta tiene más de 30 días de antigüedad.', 'error');
            return;
        }

        // 2. Obtener la Obra Social del cliente
        $cliente = Client::find($this->cliente_id);
        $obraSocial = $cliente->obrasSociales()->first();

        if (!$obraSocial) {
            $this->notify('El cliente no tiene una Obra Social activa.', 'error');
            return;
        }

        // 3. Simular cálculo de descuentos basado en el Vademécum
        $descuentoAcumulado = 0;
        
        foreach ($this->carrito as $item) {
            $cobertura = \DB::table('obra_social_medicine')
                ->where('obra_social_id', $obraSocial->id)
                ->where('medicine_id', $item['id'])
                ->first();

            if ($cobertura && $cobertura->discount_percentage > 0) {
                $montoItem = $item['price'] * $item['cantidad'];
                $descuentoAcumulado += ($montoItem * ($cobertura->discount_percentage / 100));
            }
        }

        // 4. Resultado de la "Simulación"
        $this->os_discount_amount = round($descuentoAcumulado, 2);
        $this->authorization_code = 'AUTH-' . strtoupper(bin2hex(random_bytes(3)));
        $this->is_validated = true;
        
        Flux::modal('validation-modal')->close();
        $this->notify("Autorización: {$this->authorization_code}. Descuento aplicado: $" . number_format($this->os_discount_amount, 2), 'success');
    }

    private function recalculateOSDiscount()
    {
        $this->is_validated = false;
        // Si no está validado o no hay cliente, el descuento es CERO
        if (!$this->is_validated || !$this->cliente_id) {
            $this->os_discount_amount = 0;
            return;
        }

        $cliente = Client::find($this->cliente_id);
        $obraSocial = $cliente?->obrasSociales()->first();

        if (!$obraSocial || empty($this->carrito)) {
            $this->os_discount_amount = 0;
            $this->is_validated = false; // Si el carrito está vacío, invalidamos
            return;
        }

        $descuentoAcumulado = 0;
        
        foreach ($this->carrito as $item) {
            $cobertura = \DB::table('obra_social_medicine')
                ->where('obra_social_id', $obraSocial->id)
                ->where('medicine_id', $item['id'])
                ->first();

            if ($cobertura && $cobertura->discount_percentage > 0) {
                $montoItem = $item['price'] * $item['cantidad'];
                $descuentoAcumulado += ($montoItem * ($cobertura->discount_percentage / 100));
            }
        }

        $this->os_discount_amount = round($descuentoAcumulado, 2);
    }

    public function quitarCliente()
    {
        $this->reset(['cliente_id', 'search_cliente', 'is_validated', 'os_discount_amount', 'authorization_code']);
        // Al quitar al cliente, forzamos que el total se limpie de beneficios de OS
        $this->notify('Cliente desvinculado. Se han removido los descuentos de Obra Social.', 'info');
        $this->recalculateOSDiscount();
    }

    #[Computed]
    public function subtotal()
    {
        return collect($this->carrito)->sum(fn ($item) => $item['price'] * $item['cantidad']);
    }

    public function updatedPromotionId($id)
    {
        if (! $id) {
            $this->global_adjustment = 0;

            return;
        }

        $promo = Promotion::find($id);
        $sub = $this->subtotal;

        if ($promo->type === 'discount') {
            // Descuento: Guardamos como NEGATIVO
            $this->global_adjustment = -($sub * ($promo->value / 100));
        } else {
            // Recargo: Guardamos como POSITIVO
            $this->global_adjustment = ($sub * ($promo->value / 100));
        }
    }

    public function updatedCarrito()
    {
        if ($this->promotion_id) {
            $this->updatedPromotionId($this->promotion_id);
        }
        $this->recalculateOSDiscount();
    }

    public function procesarVenta()
{
    // 1. VALIDACIONES INICIALES DE SESIÓN Y CARRITO
    if (!$this->cajaActiva) {
        $this->notify('Error: Debes abrir caja antes de vender.', 'error');
        return;
    }

    if (empty($this->carrito)) {
        $this->notify('El carrito está vacío.', 'error');
        return;
    }

    // 2. LÓGICA DE INNOVACIÓN: VALIDACIÓN DE RECETA MÉDICA
    $necesitaReceta = collect($this->carrito)->contains('requires_prescription', true);

    if ($necesitaReceta) {
        // Regla de Negocio: No se vende bajo receta a Consumidor Final (DNI es obligatorio)
        if (!$this->cliente_id) {
            $this->notify('RESTRICCIÓN LEGAL: Los medicamentos bajo receta requieren vincular un cliente identificado.', 'error');
            return;
        }

        // Regla de Integridad: Verificar que el archivo temporal sea válido y exista
        $esArchivoValido = $this->receta_pdf instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

        if (!$this->receta_pdf || !$esArchivoValido) {
            $this->notify('Error: La receta es obligatoria. Por favor, adjunte el archivo PDF nuevamente.', 'error');
            $this->receta_pdf = null; // Forzamos el nulo para limpiar basura
            return;
        }
    }

    // 3. VALIDACIÓN DE MONTOS Y AUTORIZACIÓN
    $totalPagado = collect($this->pagos_realizados)->sum('monto');
    $montoVenta = round((float) $this->totalFinal, 2);
    $pagadoReal = round((float) $totalPagado, 2);

    if ($pagadoReal > $montoVenta + 0.01) {
        $this->notify('Error: El monto pagado supera el total del comprobante.', 'error');
        return;
    }

    if ($pagadoReal < $montoVenta && !$this->cliente_id) {
        $this->notify('Falta cubrir $' . number_format($montoVenta - $pagadoReal, 2) . '. Seleccione un cliente para cuenta corriente.', 'warning');
        return;
    }

    $this->authorize('facturacion.emitir');

    // Validación formal de Laravel
    $this->validate([
        'tipo_comprobante' => 'required',
        'receta_pdf' => $necesitaReceta ? 'required|file|mimes:pdf|max:2048' : 'nullable',
    ]);

    // 4. TRANSACCIÓN ATÓMICA DE BASE DE DATOS
    try {
        $facturaID = \DB::transaction(function () use ($montoVenta, $pagadoReal, $necesitaReceta) {
            $estadoFactura = ($pagadoReal >= $montoVenta - 0.01) ? 'PAGADO' : 'PENDIENTE';

            // A. Crear la Factura
            $factura = \App\Models\Factura::create([
                'tipo_comprobante' => $this->tipo_comprobante,
                'fecha_emision'    => now(),
                'total'            => $this->totalFinal,
                'ajuste_global'    => $this->global_adjustment,
                'estado'           => $estadoFactura,
                'user_id'          => Auth::id(),
                'cliente_id'       => $this->cliente_id,
            ]);

            // B. Gestión de Receta Digital (Subida a Supabase)
            if ($necesitaReceta && $this->receta_pdf) {
                // El nombre incluye ID de factura para evitar colisiones
                $nombreArchivo = 'receta_factura_' . $factura->id . '.pdf';
                $path = $this->receta_pdf->storeAs('prescriptions', $nombreArchivo, 'supabase');

                \App\Models\Prescription::create([
                    'factura_id' => $factura->id,
                    'client_id'  => $this->cliente_id,
                    'file_path'  => $path,
                    'doctor_license' => $this->doctor_license, // Guardamos Matrícula
                    'prescription_date' => $this->prescription_date, // Guardamos Fecha
                    'authorization_code' => $this->authorization_code, // Guardamos Código AUTH
                ]);
            }

            // C. Procesar Ítems y Stock
            foreach ($this->carrito as $item) {
                \App\Models\FacturaDetalle::create([
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $item['price'],
                    'descuento'       => 0,
                    'factura_id'      => $factura->id,
                    'product_id'      => $item['product_id'],
                ]);

                // Descontar del stock global de la medicina
                $stockGlobal = \App\Models\Stock::where('medicine_id', $item['id'])->first();
                if ($stockGlobal) {
                    $stockGlobal->cantidad_actual -= $item['cantidad'];
                    $stockGlobal->save();
                }

                // Descontar de lotes físicos (Lógica FIFO por fecha de vencimiento)
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
                        'quantity' => $aQuitar,
                    ]);
                    $cantidadRestante -= $aQuitar;
                }
            }

            // D. Registrar Movimientos de Caja
            foreach ($this->pagos_realizados as $pago) {
                \App\Models\MovimientoCaja::create([
                    'tipo_movimiento'  => 'INGRESO',
                    'monto'            => $pago['monto'],
                    'motivo'           => "Venta #{$factura->id} - Pago: {$pago['nombre']}",
                    'fecha_movimiento' => now(),
                    'id_medio_pago'    => $pago['medio_id'],
                    'id_caja'          => $this->cajaActiva->id,
                    'user_id'          => Auth::id(),
                    'factura_id'       => $factura->id,
                ]);
            }

            return $factura->id;
        });

        // 5. FINALIZACIÓN Y NOTIFICACIÓN
        $this->ultimaFacturaId = $facturaID;
        
        \App\Events\StockActualizado::dispatch();
        
        $this->limpiarVenta();

        $action = \App\Models\Setting::where('key', 'post_sale_action')->first()?->value ?? 'preguntar';
        
        if ($action === 'auto_imprimir') {
            $url = route('factura.imprimir', ['id' => $facturaID]);
            $this->dispatch('abrir-impresion', url: $url);
        } elseif ($action === 'preguntar') {
            Flux::modal('exito-venta-modal')->show();
        } else {
            $this->notify('Venta procesada con éxito.', 'success');
        }

        $this->reset([
            'carrito', 'pagos_realizados', 'tipo_comprobante',
            'cliente_id', 'search_cliente', 'global_adjustment',
            'monto_pago_actual', 'medio_pago_id', 'receta_pdf',
            'is_validated', 'authorization_code', 'os_discount_amount', // <-- AGREGADO
            'doctor_license', 'prescription_date' // <-- AGREGADO
        ]);

        unset($this->totalFinal, $this->montoRestante, $this->subtotal);

    } catch (\Exception $e) {
        $this->notify('Error Crítico: ' . $e->getMessage(), 'error');
        \Illuminate\Support\Facades\Log::error("Fallo en proceso de venta: " . $e->getMessage());
    }
}

    public function generarPdfStream($id)
    {
        $factura = Factura::with(['user', 'cliente', 'details.product', 'pagos.medioPago'])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.factura', [
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
            'monto_pago_actual', 'medio_pago_id', 'receta_pdf',
        ]);

        unset($this->totalFinal, $this->montoRestante, $this->subtotal);
    }

    #[Computed]
    public function historialVentas()
    {
        return Factura::query()
            ->with(['user', 'pagos.medioPago', 'cliente'])
            ->when(! Auth::user()->hasRole('admin'), function ($q) {
                $q->where('user_id', Auth::id());
            })
            ->when($this->filtroEstado, function ($q) {
                $q->where('estado', $this->filtroEstado);
            })
            ->when($this->fecha_desde, function ($q) {
                $q->whereDate('fecha_emision', '>=', $this->fecha_desde);
            })
            ->when($this->fecha_hasta, function ($q) {
                $q->whereDate('fecha_emision', '<=', $this->fecha_hasta);
            })
            ->orderBy('fecha_emision', 'desc')
            ->paginate(10);
    }

    #[Computed]
    public function tieneOS()
    {
        if (!$this->cliente_id) return false;
        
        return \App\Models\Client::find($this->cliente_id)
            ->obrasSociales()
            ->exists();
    }

    #[Computed]
    public function tieneProductosCubiertos()
    {
        if (!$this->cliente_id || empty($this->carrito)) {
            return false;
        }

        $cliente = Client::find($this->cliente_id);
        $obraSocial = $cliente?->obrasSociales()->first();

        if (!$obraSocial) {
            return false;
        }

        // Obtenemos los IDs de los medicamentos en el carrito
        $idsEnCarrito = collect($this->carrito)->pluck('id')->toArray();

        // Buscamos si alguno de esos IDs existe en el Vademécum de esta OS con descuento > 0
        return \DB::table('obra_social_medicine')
            ->where('obra_social_id', $obraSocial->id)
            ->whereIn('medicine_id', $idsEnCarrito)
            ->where('discount_percentage', '>', 0)
            ->exists();
    }

    #[Computed]
    public function medicines(): Collection
    {
        $lk = $this->likeOperator();

        $collection = Medicine::query()
            ->with(['product', 'stock'])
            ->leftJoin('stocks', 'medicines.id', '=', 'stocks.medicine_id')
            ->join('products', 'medicines.product_id', '=', 'products.id')
            ->where('products.status', true)
            ->where(function ($q) use ($lk) {
                $q->where('products.name', $lk, "%{$this->search}%")
                    ->orWhere('medicines.presentation_name', $lk, "%{$this->search}%");
            })
            ->when($this->filterGroup, function ($q) {
                $q->where('medicines.group_id', $this->filterGroup);
            })
            ->orderByRaw('CASE WHEN stocks.cantidad_actual > 0 THEN 0 ELSE 1 END ASC')
            ->orderBy('products.name', 'asc')
            ->select('medicines.*')
            ->get();

        Log::info('[VentaManager.medicines()] Base de Datos Consultada: hidratando '.$collection->count().' items.');

        // Log individual solo del primer item o de uno en específico para diagnosticar velozmente
        if ($collection->count() > 0) {
            $first = $collection->first();
            Log::info("[VentaManager.medicines()] Muestra Audit: Medicina #{$first->id} ('{$first->product->name}'), Stock Leído: ".($first->stock?->cantidad_actual ?? '0'));
        }

        return $collection;
    }

    public function validatePrescription()
    {
        Flux::modal('validation-modal')->show();
    }

    public function render()
    {
        $maxDays = (int) (Setting::where('key', 'price_max_days')->first()?->value ?? 30);

        return view('livewire.user.venta-manager', [
            'mediosPago' => MedioPago::all(),
            'maxDays' => $maxDays,
            'groups' => Group::orderBy('name')->get(),
        ])->layout('components.layouts.app');
    }
}
