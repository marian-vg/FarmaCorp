<?php

namespace App\Livewire\Admin;

use App\Models\Group;
use App\Models\Medicine;
use App\Models\Product;
use App\Traits\Notifies;
use Flux\Flux;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Alta de Medicamento'])]
class MedicineManager extends Component
{
    use Notifies, WithPagination;

    public string $search = '';

    public bool $filterPsychotropic = false;

    public string $filterGroup = '';

    public string $stockSort = '';

    public ?Medicine $viewingMedicine = null;

    public array $context = [
        'product_id' => '',
        'presentation_name' => '',
        'price' => null,
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

    public function updatedFilterGroup()
    {
        $this->resetPage();
    }

    public function updatedStockSort()
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
            'context.presentation_name' => 'nullable|string|max:255',
            'context.price' => 'required|numeric|min:0',
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
            'presentation_name' => $this->context['presentation_name'],
            'price' => $this->context['price'],
            'group_id' => $this->context['group_id'],
            'level' => $this->context['level'],
            'leaflet' => $this->context['leaflet'],
            'expiration_date' => $this->context['expiration_date'] ?: null,
            'is_psychotropic' => (bool) $this->context['is_psychotropic'],
        ]);

        Flux::modal('medicine-form')->close();
        $this->reset('context');
        $this->notify('Medicamento guardado exitosamente.', 'success');
    }

    public function render()
    {
        $query = Medicine::query()
            ->with(['product', 'stock', 'group'])
            ->join('products', 'medicines.product_id', '=', 'products.id')
            ->leftJoin('groups', 'medicines.group_id', '=', 'groups.id')
            ->leftJoin('stocks', 'medicines.id', '=', 'stocks.medicine_id')
            ->select('medicines.*', 'stocks.cantidad_actual');

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('products.name', 'ilike', '%'.$this->search.'%')
                    ->orWhere('medicines.presentation_name', 'ilike', '%'.$this->search.'%')
                    ->orWhere('medicines.level', 'ilike', '%'.$this->search.'%')
                    ->orWhere('groups.name', 'ilike', '%'.$this->search.'%');
            });
        }

        if ($this->filterPsychotropic) {
            $query->where('medicines.is_psychotropic', true);
        }
        if ($this->filterGroup) {
            $query->where('medicines.group_id', $this->filterGroup);
        }

        if ($this->stockSort === 'asc') {
            $query->orderByRaw('COALESCE(stocks.cantidad_actual, 0) ASC');
        } elseif ($this->stockSort === 'desc') {
            $query->orderByRaw('COALESCE(stocks.cantidad_actual, 0) DESC');
        } else {
            $query->orderBy('medicines.id', 'desc');
        }

        $medicines = $query->paginate(12);

        $availableProducts = Product::where('status', true)
            ->doesntHave('medicine')
            ->orderBy('name')
            ->get();

        $groups = Cache::remember('groups_all', 86400, fn () => Group::orderBy('name')->get());

        return view('livewire.admin.medicine-manager', [
            'medicines' => $medicines,
            'availableProducts' => $availableProducts,
            'groups' => $groups,
        ]);
    }
}
