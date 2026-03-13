<?php

namespace App\Livewire\Admin;

use App\Events\StockActualizado;
use App\Models\Batch;
use App\Models\Group;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Traits\Notifies;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Egreso de Stock'])]
class StockEgresoManager extends Component
{
    use Notifies, WithPagination;

    public function mount()
    {
        $this->authorize('stock.egreso');
    }

    public $search = '';

    public $filterGroup = '';

    public function updatedFilterGroup()
    {
        $this->resetPage();
    }

    // Form properties
    public $batch_id;

    public $quantity_to_remove;

    public $reason;

    public $current_stock_display = 0;

    protected function rules()
    {
        return [
            'batch_id' => 'required|exists:batches,id',
            'quantity_to_remove' => 'required|integer|min:1|max:'.$this->current_stock_display,
            'reason' => 'required|string|in:devolucion_proveedor,merma_rotura,robo,destruccion_vencimiento',
        ];
    }

    public function selectBatch($id, $current_quantity)
    {
        $this->batch_id = $id;
        $this->current_stock_display = $current_quantity;
        $this->resetValidation();
        $this->quantity_to_remove = '';
        $this->reason = '';

        Flux::modal('egreso-modal')->show();
    }

    public function save()
    {
        $this->validate();

        $batch = Batch::findOrFail($this->batch_id);

        if ($this->quantity_to_remove > $batch->current_quantity) {
            $this->addError('quantity_to_remove', 'La cantidad a retirar no puede superar el stock actual del lote ('.$batch->current_quantity.').');

            return;
        }

        DB::transaction(function () use ($batch) {
            // Paso A: Restar la cantidad en el Lote (Lo que ya hacía)
            $batch->current_quantity -= $this->quantity_to_remove;
            $batch->save();

            // Determinar motivo (Lógica de tu compañero)
            $mappedReason = 'ajuste';
            switch ($this->reason) {
                case 'devolucion_proveedor': $mappedReason = 'devolucion';
                    break;
                case 'merma_rotura': $mappedReason = 'merma';
                    break;
                case 'robo': $mappedReason = 'robo';
                    break;
                case 'destruccion_vencimiento': $mappedReason = 'vencimiento';
                    break;
            }

            // Paso B: Descontar cantidad en el Stock Global de la Presentación Mëdica
            $stockGlobal = Stock::where('medicine_id', $batch->medicine_id)->first();

            if ($stockGlobal) {
                $stockGlobal->cantidad_actual -= $this->quantity_to_remove;
                // Prevenir stock negativo global
                if ($stockGlobal->cantidad_actual < 0) {
                    $stockGlobal->cantidad_actual = 0;
                }
                $stockGlobal->fecha_actualizacion = now(); // Corrección tipográfica DB
                $stockGlobal->save();
            }

            // Paso C: Crear Movimiento de Stock
            StockMovement::create([
                'batch_id' => $batch->id,
                'user_id' => Auth::id(),
                'type' => 'egreso',
                'reason' => $mappedReason,
                'quantity' => $this->quantity_to_remove,
            ]);
        });

        Flux::modal('egreso-modal')->close();
        StockActualizado::dispatch();
        $this->notify('Egreso registrado con éxito.', 'success');
        $this->reset(['batch_id', 'quantity_to_remove', 'reason', 'current_stock_display']);
    }

    public function render()
    {
        $batches = Batch::search($this->search)
            ->query(function ($builder) {
                $builder->where('current_quantity', '>', 0)
                    ->join('medicines', 'batches.medicine_id', '=', 'medicines.id')
                    ->join('products', 'medicines.product_id', '=', 'products.id')
                    ->select('batches.*') // Strict select to avoid ID collisions
                    ->with(['medicine.product', 'medicine.group']);

                if ($this->filterGroup !== '') {
                    $builder->where('medicines.group_id', $this->filterGroup);
                }
            })
            ->paginate(12);

        return view('livewire.admin.stock-egreso-manager', [
            'batches' => $batches,
            'groups' => Group::orderBy('name')->get(),
        ]);
    }
}
