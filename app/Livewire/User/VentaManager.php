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
        $this->facturaSeleccionada = Factura::with('details.product')->findOrFail($facturaId);
        \Flux::modal('detalle-venta-modal')->show();
    }

    #[Computed]
    public function cajaActiva()
    {
        return Caja::where('user_id', Auth::id())->whereNull('fecha_cierre')->first();
    }

    public function agregarAlCarrito(Product $product)
    {
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
        if (!$this->cajaActiva) {
            $this->dispatch('notify', message: 'Error: Debes abrir caja antes de vender.', variant: 'danger');
            return;
        }

        if (empty($this->carrito)) {
            $this->dispatch('notify', message: 'El carrito está vacío.', variant: 'warning');
            return;
        }

        if ($this->es_cuenta_corriente && !$this->cliente_id) {
            $this->dispatch('notify', message: 'Debes seleccionar un cliente para Cuenta Corriente.', variant: 'danger');
            return;
        }

        $this->validate([
            'medio_pago_id' => $this->es_cuenta_corriente ? 'nullable' : 'required|exists:medio_pagos,id',
            'tipo_comprobante' => 'required',
        ]);

        \DB::transaction(function () {
            $factura = Factura::create([
                'tipo_comprobante' => $this->tipo_comprobante,
                'fecha_emision'    => now(),
                'total'            => $this->subtotal,
                'estado'           => $this->es_cuenta_corriente ? 'PENDIENTE' : 'PAGADO', 
                'user_id'          => Auth::id(),
                'cliente_id'       => $this->cliente_id,
                'medio_pago_id'    => $this->es_cuenta_corriente ? null : $this->medio_pago_id, 
            ]);

            foreach ($this->carrito as $item) {
                \App\Models\FacturaDetalle::create([
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $item['price'],
                    'descuento'       => 0,
                    'factura_id'      => $factura->id,
                    'product_id'      => $item['id'],
                ]);

                // Actualizar Stock Global
                $stockGlobal = \App\Models\Stock::where('product_id', $item['id'])->first();
                if ($stockGlobal) {
                    $stockGlobal->cantidad_actual -= $item['cantidad'];
                    $stockGlobal->save();
                }

                // Descuento por Lotes (FIFO)
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

            if (!$this->es_cuenta_corriente) {
                MovimientoCaja::create([
                    'tipo_movimiento'  => 'INGRESO',
                    'monto'            => $this->subtotal,
                    'motivo'           => "Venta {$this->tipo_comprobante}: #" . str_pad($factura->id, 6, '0', STR_PAD_LEFT),
                    'fecha_movimiento' => now(),
                    'id_medio_pago'    => $this->medio_pago_id,
                    'id_caja'          => $this->cajaActiva->id,
                    'user_id'          => Auth::id(),
                ]);
            }
        });

        $this->reset(['carrito', 'medio_pago_id', 'tipo_comprobante', 'cliente_id', 'search_cliente', 'es_cuenta_corriente']);
        $this->dispatch('notify', message: 'Operación registrada con éxito.');
    }

    #[Computed]
    public function historialVentas()
    {
        return Factura::query()
            ->with(['user', 'medioPago'])
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
        $products = Product::query()
            ->leftJoin('stocks', 'products.id', '=', 'stocks.product_id')
            ->where('products.status', true)
            ->where('products.name', 'like', "%{$this->search}%")
            ->orderByRaw('CASE WHEN stocks.cantidad_actual > 0 THEN 0 ELSE 1 END ASC')
            ->orderBy('products.name', 'asc')
            ->select('products.*')
            ->with('stock') 
            ->get();

        return view('livewire.user.venta-manager', [
            'products' => $products,
            'mediosPago' => MedioPago::all()
        ])->layout('components.layouts.app');
    }
}