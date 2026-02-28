<?php

namespace App\Livewire\User;

use App\Models\Product;
use App\Models\Caja;
use App\Models\MovimientoCaja;
use App\Models\MedioPago;
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
        if (!$this->cajaActiva) {
            Flux::toast('Error: Debes abrir caja antes de vender.', variant: 'danger');
            return;
        }

        if (empty($this->carrito)) {
            Flux::toast('El carrito está vacío.', variant: 'warning');
            return;
        }

        $this->validate(['medio_pago_id' => 'required|exists:medio_pagos,id']);

        // RF-08: Registrar el ingreso en la caja [cite: 121, 123]
        MovimientoCaja::create([
            'tipo_movimiento' => 'INGRESO',
            'monto' => $this->subtotal,
            'motivo' => 'Venta de productos (POS)',
            'fecha_movimiento' => now(),
            'id_medio_pago' => $this->medio_pago_id,
            'id_caja' => $this->cajaActiva->id,
            'user_id' => Auth::id(),
        ]);

        // Nota: Aquí se conectará luego el descuento de stock (RF-14) [cite: 145]
        
        $this->reset(['carrito', 'medio_pago_id']);
        Flux::toast('Venta realizada con éxito.', variant: 'success');
    }

    #[Computed]
    public function historialVentas()
    {
        // RF-11/12: El admin ve todo, el empleado solo lo suyo
        return MovimientoCaja::query()
            ->with(['user', 'medioPago'])
            ->where('motivo', 'Venta de productos (POS)')
            ->when(!Auth::user()->hasRole('admin'), function($q) {
                $q->where('user_id', Auth::id());
            })
            ->orderBy('fecha_movimiento', 'desc')
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