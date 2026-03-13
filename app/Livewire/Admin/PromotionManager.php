<?php

namespace App\Livewire\Admin;

use App\Models\Promotion;
use App\Traits\Notifies;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class PromotionManager extends Component
{
    use Notifies, WithPagination;

    public $search = '';

    public $promotionId;

    // Propiedades del formulario
    public $name;

    public $value;

    public $type = 'discount';

    public $status = true;

    protected $rules = [
        'name' => 'required|string|min:3|max:255',
        'value' => 'required|numeric|min:0|max:100',
        'type' => 'required|in:discount,surcharge',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->reset(['name', 'value', 'type', 'status', 'promotionId']);
        Flux::modal('promotion-modal')->show();
    }

    public function edit(Promotion $promotion)
    {
        $this->promotionId = $promotion->id;
        $this->name = $promotion->name;
        $this->value = $promotion->value;
        $this->type = $promotion->type;
        $this->status = $promotion->status;

        Flux::modal('promotion-modal')->show();
    }

    public function save()
    {
        $this->validate();

        Promotion::updateOrCreate(
            ['id' => $this->promotionId],
            [
                'name' => $this->name,
                'value' => $this->value,
                'type' => $this->type,
                'status' => $this->status,
            ]
        );

        $this->notify($this->promotionId ? 'Regla actualizada.' : 'Regla creada con éxito.', 'success');
        Flux::modal('promotion-modal')->close();
        $this->reset();
    }

    public function toggleStatus(Promotion $promotion)
    {
        $promotion->update(['status' => ! $promotion->status]);
        $this->notify('Estado actualizado.', 'success');
    }

    public function delete(Promotion $promotion)
    {
        $promotion->delete();
        $this->notify('Regla eliminada del sistema.', 'success');
    }

    public function render()
    {
        $promotions = Promotion::where('name', 'like', "%{$this->search}%")
            ->orderBy('type', 'asc')
            ->orderBy('value', 'desc')
            ->paginate(10);

        return view('livewire.admin.promotion-manager', [
            'promotions' => $promotions,
        ]);
    }
}
