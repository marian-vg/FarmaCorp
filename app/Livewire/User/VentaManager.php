<?php

namespace App\Livewire\User;

use App\Models\Product;
use App\Models\Caja;
use App\Models\MovimientoCaja;
use App\Models\MedioPago;
use App\Models\Factura;
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
    public $medio_pago_id = '';
    public $tabActiva = 'vender';

    // Buscamos la caja activa del empleado
    public function getCajaActivaProperty()
    {
        return Caja::where('user_id', Auth::id())->whereNull('fecha_cierre')->first();
    }

    public function agregarAlCarrito(Product $product)
    {
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
        Flux::toast('Añadido: ' . $product->name);
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
        // 1. Validaciones iniciales
        if (!$this->cajaActiva) {
            Flux::toast('Error: Debes abrir caja antes de vender.', variant: 'danger');
            return;
        }

        if (empty($this->carrito)) {
            Flux::toast('El carrito está vacío.', variant: 'warning');
            return;
        }

        $this->validate(['medio_pago_id' => 'required|exists:medio_pagos,id']);

        // 2. Proceso de Guardado Multitabla (Transaccional)
        \DB::transaction(function () {
            $factura = Factura::create([
                'tipo_comprobante' => 'VENTA-POS', // [cite: 505]
                'fecha_emision'    => now(),        // [cite: 505]
                'total'            => $this->subtotal, // [cite: 506]
                'estado'           => 'PAGADO',      // [cite: 507]
                'user_id'          => Auth::id(),    // credencial (FK) [cite: 508]
                'cliente_id'       => null,          // ID_Cliente (FK) [cite: 509]
                'medio_pago_id'    => $this->medio_pago_id, 
            ]);

            // B. Crear los Detalles (Los productos del carrito) [cite: 525]
            foreach ($this->carrito as $item) {
                \App\Models\FacturaDetalle::create([
                    'cantidad'        => $item['cantidad'],    // cantidad [cite: 526]
                    'precio_unitario' => $item['price'],       // precio_unitario [cite: 527]
                    'descuento'       => 0,                    // descuento [cite: 527]
                    'factura_id'      => $factura->id,         // ID_Factura (FK) [cite: 528]
                    'product_id'      => $item['id'],          // ID_Producto (FK) [cite: 529]
                ]);
                
                // Aquí podrías agregar el descuento de stock más adelante (RF-14) [cite: 122]
            }

            // C. Registrar el ingreso en la Caja (Lo que ya hacías) [cite: 98, 466]
            MovimientoCaja::create([
                'tipo_movimiento'  => 'INGRESO',
                'monto'            => $this->subtotal,
                'motivo'           => "Venta POS: #" . str_pad($factura->id, 6, '0', STR_PAD_LEFT),
                'fecha_movimiento' => now(),
                'id_medio_pago'    => $this->medio_pago_id,
                'id_caja'          => $this->cajaActiva->id,
                'user_id'          => Auth::id(),
            ]);
        });

        // 3. Limpieza y Notificación
        $this->reset(['carrito', 'medio_pago_id']);
        Flux::toast('Venta procesada y facturada correctamente.', variant: 'success');
    }

    #[Computed]
    public function historialVentas()
    {
        // Cambiamos MovimientoCaja por Factura para que el historial muestre productos [cite: 503]
        return Factura::query()
            ->with(['user', 'details.product', 'medioPago'])
            ->when(!Auth::user()->hasRole('admin'), function($q) {
                $q->where('user_id', Auth::id());
            })
            ->orderBy('fecha_emision', 'desc')
            ->paginate(10);
    }

    public function render()
    {
        $products = Product::search($this->search)
            ->query(function ($query) {
                $query->where('status', true);
            })
            ->get();

        return view('livewire.user.venta-manager', [
            'products' => $products,
            'mediosPago' => MedioPago::all()
        ])->layout('components.layouts.app');
    }
}