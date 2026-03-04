<?php

namespace App\Livewire\Admin;

use App\Models\StockMovement;
use Livewire\Component;
use Livewire\WithPagination;

class StockHistorialManager extends Component
{
    use WithPagination;

    public $search = '';

    public function render()
    {
        $movements = StockMovement::search($this->search)
            ->query(function ($builder) {
                $builder->join('batches', 'stock_movements.batch_id', '=', 'batches.id')
                        ->join('medicines', 'batches.medicine_id', '=', 'medicines.product_id')
                        ->join('products', 'medicines.product_id', '=', 'products.id')
                        ->join('users', 'stock_movements.user_id', '=', 'users.id')
                        ->select('stock_movements.*')
                        ->with(['batch.medicine.product', 'user'])
                        ->latest('stock_movements.created_at');
            })
            ->paginate(15);

        return view('livewire.admin.stock-historial-manager', [
            'movements' => $movements
        ]);
    }
}
