<?php

namespace App\Livewire\Admin;

use App\Models\Batch;
use App\Models\Medicine;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Traits\Notifies;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Ingreso de Stock'])]
class StockIngresoManager extends Component
{
    use Notifies, WithPagination;

    public $search = '';

    public $filterGroup = '';

    public function updatedFilterGroup()
    {
        $this->resetPage();
    }

    // Form properties
    public $medicine_id;

    public $batch_number;

    public $expiration_date;

    public $quantity_received;

    public $minimum_stock;

    protected $rules = [
        'medicine_id' => 'required|exists:medicines,id',
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
        $this->minimum_stock;

        Flux::modal('ingreso-modal')->show();
    }

    public function save()
    {
        $this->validate();

        \DB::transaction(function () {
            // Paso A: Buscar Lote o Crearlo (Upserting para evitar Lotes Duplicados)
            $batch = Batch::where('medicine_id', $this->medicine_id)
                ->where('batch_number', $this->batch_number)
                ->first();

            if ($batch) {
                // Si el lote existe, sumamos cantidades y actualizamos vencimiento y stock mínimo
                $batch->initial_quantity += $this->quantity_received;
                $batch->current_quantity += $this->quantity_received;
                // Asumimos la fecha de vencimiento más reciente del remito
                $batch->expiration_date = $this->expiration_date;
                if ($this->minimum_stock > $batch->minimum_stock) {
                    $batch->minimum_stock = $this->minimum_stock;
                }
                $batch->save();
            } else {
                // Si no existe, lo creamos convencionalmente
                $batch = Batch::create([
                    'medicine_id' => $this->medicine_id,
                    'batch_number' => $this->batch_number,
                    'expiration_date' => $this->expiration_date,
                    'initial_quantity' => $this->quantity_received,
                    'current_quantity' => $this->quantity_received,
                    'minimum_stock' => $this->minimum_stock,
                ]);
            }

            // Paso B: Update global Stock for the Medicine
            $stock = Stock::firstOrCreate(
                ['medicine_id' => $this->medicine_id],
                ['cantidad_actual' => 0, 'stock_minimo' => 0]
            );

            $stock->cantidad_actual += $this->quantity_received;
            // Update the global minimum stock if the new batch has a higher strict limit
            if ($this->minimum_stock > $stock->stock_minimo) {
                $stock->stock_minimo = $this->minimum_stock;
            }
            $stock->save();

            // Paso C: Crear Movimiento de Stock (StockMovement)
            StockMovement::create([
                'batch_id' => $batch->id,
                'user_id' => \Auth::id(),
                'type' => 'ingreso',
                'reason' => 'compra',
                'quantity' => $this->quantity_received,
            ]);
        });

        Flux::modal('ingreso-modal')->close();
        $this->dispatch('stock-actualizado');
        $this->notify('Ingreso registrado con éxito.', 'success');
        $this->reset(['medicine_id', 'batch_number', 'expiration_date', 'quantity_received', 'minimum_stock']);
    }

    public function render()
    {
        // Usa Scout o fallbacks a ilike para buscar medicamentos reales
        $medicines = Medicine::search($this->search)
            ->query(function ($builder) {
                $builder->with(['product', 'group'])
                    ->join('products', 'medicines.product_id', '=', 'products.id')
                    ->leftJoin('groups', 'medicines.group_id', '=', 'groups.id')
                    ->select('medicines.*');

                if ($this->filterGroup !== '') {
                    $builder->where('medicines.group_id', $this->filterGroup);
                }
            })
            ->paginate(12);

        return view('livewire.admin.stock-ingreso-manager', [
            'medicines' => $medicines,
            'groups' => \App\Models\Group::orderBy('name')->get(),
        ]);
    }
}
