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

    #[Computed]
    public function clientes()
    {
        // Usamos Laravel Scout para una búsqueda de alto rendimiento (RF-08)
        return \App\Models\Client::search($this->search_cliente)
            ->take(5)
            ->get();
    }

    // Buscamos la caja activa del empleado
    public function getCajaActivaProperty()
    {
        return Caja::where('user_id', Auth::id())->whereNull('fecha_cierre')->first();
    }

    public function agregarAlCarrito(Product $product)
    {
        // Validación RF-04: Exigir tipo de comprobante antes de cargar 
        if (!$this->tipo_comprobante) {
            $this->dispatch('notify', message: 'Primero selecciona un tipo de comprobante.', variant: 'warning');
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

    public function getSubtotalProperty()
    {
        return collect($this->carrito)->sum(fn($item) => $item['price'] * $item['cantidad']);
    }

    public function procesarVenta()
    {
        if (!$this->cajaActiva) {
            $this->dispatch('notify', message: 'Error: Debes abrir caja antes de vender.', variant: 'danger');
            return;
        }

        $this->validate([
            'medio_pago_id' => 'required|exists:medio_pagos,id',
            'tipo_comprobante' => 'required', // RF-04 
        ]);

        \DB::transaction(function () {
            // RF-13: Emitir factura básica vinculando productos y cliente 
            $factura = Factura::create([
                'tipo_comprobante' => $this->tipo_comprobante, // RF-04 [cite: 505]
                'fecha_emision'    => now(),                   // [cite: 505]
                'total'            => $this->subtotal,         // [cite: 506]
                'estado'           => 'PAGADO',                // [cite: 507]
                'user_id'          => Auth::id(),              // Credencial (FK) [cite: 508]
                'cliente_id'       => $this->cliente_id,       // RF-11 y RF-23 [cite: 118, 509]
                'medio_pago_id'    => $this->medio_pago_id, 
            ]);

            foreach ($this->carrito as $item) {
                \App\Models\FacturaDetalle::create([
                    'cantidad'        => $item['cantidad'],    // [cite: 526]
                    'precio_unitario' => $item['price'],       // [cite: 527]
                    'descuento'       => 0,                    // [cite: 527]
                    'factura_id'      => $factura->id,         // [cite: 528]
                    'product_id'      => $item['id'],          // [cite: 529]
                ]);
            }

            MovimientoCaja::create([
                'tipo_movimiento'  => 'INGRESO',
                'monto'            => $this->subtotal,
                'motivo'           => "Venta {$this->tipo_comprobante}: #" . str_pad($factura->id, 6, '0', STR_PAD_LEFT),
                'fecha_movimiento' => now(),
                'id_medio_pago'    => $this->medio_pago_id,
                'id_caja'          => $this->cajaActiva->id,
                'user_id'          => Auth::id(),
            ]);
        });

        $this->reset(['carrito', 'medio_pago_id', 'tipo_comprobante', 'cliente_id', 'search_cliente']);
        $this->dispatch('notify', message: 'Venta procesada con éxito.');
    }

    #[Computed]
    public function historialVentas()
    {
        // Cambiamos MovimientoCaja por Factura para que el historial muestre productos [cite: 503]
        return Factura::query()
            ->with(['user', 'details.product'])
            ->when(!Auth::user()->hasRole('admin'), function($q) {
                $q->where('user_id', Auth::id());
            })
            ->orderBy('fecha_emision', 'desc')
            ->paginate(10);
    }

    public function render()
    {
        $products = Product::where('status', true)
            ->where('name', 'like', "%{$this->search}%")
            ->get();

        return view('livewire.user.venta-manager', [
            'products' => $products,
            'mediosPago' => MedioPago::all()
        ])->layout('components.layouts.app');
    }
}