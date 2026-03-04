<?php

namespace App\Livewire\Admin;

use App\Models\Group;
use App\Models\Medicine;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Flux\Flux;

#[Layout('components.layouts.app', ['title' => 'Alta de Medicamento'])]
class MedicineManager extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $filterPsychotropic = false;
    public ?Medicine $viewingMedicine = null;

    public array $context = [
        'product_id' => '',
        'group_id' => '',
        'level' => '',
        'leaflet' => '',
        'expiration_date' => null,
        'is_psychotropic' => false,
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterPsychotropic()
    {
        $this->resetPage();
    }

    public function viewLeaflet(Medicine $medicine)
    {
        // Must load the product to display the name in the modal
        $medicine->load('product');
        $this->viewingMedicine = $medicine;
        Flux::modal('leaflet-modal')->show();
    }

    public function createMedicine()
    {
        $this->reset('context');
        Flux::modal('medicine-form')->show();
    }

    public function saveMedicine()
    {
        $this->validate([
            'context.product_id' => 'required|exists:products,id|unique:medicines,product_id',
            'context.group_id' => 'required|exists:groups,id',
            'context.level' => 'nullable|string|max:255',
            'context.leaflet' => 'nullable|string',
            'context.expiration_date' => 'nullable|date',
            'context.is_psychotropic' => 'boolean',
        ], [
            'context.product_id.unique' => 'Este producto ya ha sido catalogado como medicamento.',
        ]);

        Medicine::create([
            'product_id' => $this->context['product_id'],
            'group_id' => $this->context['group_id'],
            'level' => $this->context['level'],
            'leaflet' => $this->context['leaflet'],
            'expiration_date' => $this->context['expiration_date'] ?: null,
            'is_psychotropic' => (bool)$this->context['is_psychotropic'],
        ]);

        Flux::modal('medicine-form')->close();
        $this->reset('context');
    }

    public function render()
    {
        $medicines = Medicine::search($this->search)
            ->query(function ($query) {
                $query->with(['product', 'group']);
                
                if ($this->filterPsychotropic) {
                    $query->where('is_psychotropic', true);
                }
                $query->join('products', 'medicines.product_id', '=', 'products.id')
                    ->leftJoin('groups', 'medicines.group_id', '=', 'groups.id')
                    ->select('medicines.*');
            })
            ->paginate(12);

        $availableProducts = Product::where('status', true)
            ->doesntHave('medicine')
            ->orderBy('name')
            ->get();

        $groups = Group::orderBy('name')->get();

        return view('livewire.admin.medicine-manager', [
            'medicines' => $medicines,
            'availableProducts' => $availableProducts,
            'groups' => $groups,
        ]);
    }
}
