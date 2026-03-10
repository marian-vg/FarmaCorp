<?php

namespace App\Livewire\Admin;

use App\Models\StockMovement;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Historial de Stock'])]
class StockHistorialManager extends Component
{
    use WithPagination;

    public $search = '';

    public $filterType = '';

    public $fecha_desde = '';

    public $fecha_hasta = '';

    public function updatedFilterType()
    {
        $this->resetPage();
    }

    public function updatedFechaDesde()
    {
        $this->resetPage();
    }

    public function updatedFechaHasta()
    {
        $this->resetPage();
    }

    public function render()
    {
        $movements = StockMovement::search($this->search)
            ->query(function ($builder) {
                $builder->join('batches', 'stock_movements.batch_id', '=', 'batches.id')
                    ->join('medicines', 'batches.medicine_id', '=', 'medicines.product_id')
                    ->join('products', 'medicines.product_id', '=', 'products.id')
                    ->join('users', 'stock_movements.user_id', '=', 'users.id')
                    ->select('stock_movements.*')
                    ->with(['batch.medicine.product', 'user']);

                if ($this->filterType !== '') {
                    $builder->where('stock_movements.type', $this->filterType);
                }
                if ($this->fecha_desde !== '') {
                    $builder->whereDate('stock_movements.created_at', '>=', $this->fecha_desde);
                }
                if ($this->fecha_hasta !== '') {
                    $builder->whereDate('stock_movements.created_at', '<=', $this->fecha_hasta);
                }

                $builder->latest('stock_movements.created_at');
            })
            ->paginate(15);

        return view('livewire.admin.stock-historial-manager', [
            'movements' => $movements,
        ]);
    }
}
