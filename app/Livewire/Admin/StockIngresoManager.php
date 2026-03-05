<?php

namespace App\Livewire\Admin;

use App\Models\Batch;
use App\Models\Medicine;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Flux\Flux;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app', ['title' => 'Ingreso de Stock'])]
class StockIngresoManager extends Component
{
    use WithPagination;

    public $search = '';

    // Form properties
    public $medicine_id;
    public $batch_number;
    public $expiration_date;
    public $quantity_received;
    public $minimum_stock = 0;

    protected $rules = [
        'medicine_id' => 'required|exists:medicines,product_id',
        'batch_number' => 'required|string|max:255',
        'expiration_date' => 'required|date|after:today',
        'quantity_received' => 'required|integer|min:1',
        'minimum_stock' => 'required|integer|min:0',
    ];

    public function selectMedicine($id)
    {
        $this->medicine_id = $id;
        $this->resetValidation();
        $this->batch_number = '';
        $this->expiration_date = '';
        $this->quantity_received = '';
        $this->minimum_stock = 0;

        Flux::modal('ingreso-modal')->show();
    }

    public function save()
    {
        $this->validate();

        \DB::transaction(function () {
            // Paso A: Crear Lote (Lo que ya hace)
            $batch = \App\Models\Batch::create([
                'medicine_id' => $this->medicine_id,
                'batch_number' => $this->batch_number,
                'expiration_date' => $this->expiration_date,
                'initial_quantity' => $this->quantity_received,
                'current_quantity' => $this->quantity_received,
                'minimum_stock' => $this->minimum_stock,
            ]);

            // Paso B: Crear Movimiento (Lo que ya hace)
            \App\Models\StockMovement::create([
                'batch_id' => $batch->id,
                'user_id' => \Auth::id(),
                'type' => 'ingreso',
                'reason' => 'compra',
                'quantity' => $this->quantity_received,
            ]);

            // PASO C: Actualizar el Stock Global (Totalizador) [cite: 18]
            $stock = \App\Models\Stock::firstOrNew(['product_id' => $this->medicine_id]);

            // Si el registro es nuevo, 'cantidad_actual' será 0 o null automáticamente
            $stock->stock_minimo = $this->minimum_stock;
            $stock->cantidad_actual += $this->quantity_received;
            $stock->fecha_actualización = now();

            $stock->save();
        });

        \Flux::modal('ingreso-modal')->close();
        \Flux::toast('Lote registrado y stock global actualizado.');
        $this->reset(['medicine_id', 'batch_number', 'expiration_date', 'quantity_received', 'minimum_stock']);
    }

    public function render()
    {
        // Usa Scout o fallbacks a ilike para buscar medicamentos reales
        $medicines = Medicine::search($this->search)
            ->query(function($builder){
                $builder->with(['product', 'group'])
                    ->join('products', 'medicines.product_id', '=', 'products.id')
                    ->leftJoin('groups', 'medicines.group_id', '=', 'groups.id')
                    ->select('medicines.*');
            })    
            ->paginate(12);

        return view('livewire.admin.stock-ingreso-manager', [
            'medicines' => $medicines
        ]);
    }
}
