<?php

namespace App\Livewire\Admin;

use App\Models\Factura;
use App\Models\FacturaDetalle;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Flux\Flux;

#[Layout('components.layouts.app', ['title' => 'Historial de Ventas'])]
class SalesManager extends Component
{
    use WithPagination;

    public string $search = '';
    public $ventaSeleccionada = null;

    /**
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    #[Computed]
    public function ventas()
    {
        return Factura::search($this->search)
            ->query(function($q) {
                $q->join('users', 'facturas.user_id', '=', 'users.id')
                  ->select('facturas.*')
                  ->with(['user', 'details.product']);
            })
            ->orderBy('fecha_emision', 'desc')
            ->paginate(15);
    }

    public function verDetalle($id)
    {
        $this->ventaSeleccionada = Factura::with('details.product')->find($id);
        Flux::modal('detalle-venta-modal')->show();
    }

    public function render()
    {
        return view('livewire.admin.sales-manager');
    }
}