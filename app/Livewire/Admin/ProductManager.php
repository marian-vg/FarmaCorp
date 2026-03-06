<?php

namespace App\Livewire\Admin;

use App\Models\Group;
use App\Models\Medicine;
use App\Models\Product;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Flux\Flux;
use Illuminate\Support\Facades\Cache;

#[Layout('components.layouts.app', ['title' => 'Gestión de Productos y Medicamentos'])]
class ProductManager extends Component
{
    use WithPagination;

    public string $search = '';
    public ?Product $editingProduct = null;
    public bool $isMedicine = false;

    public array $productContext = [
        'name' => '',
        'description' => '',
        'price' => null,
        'status' => true,
    ];

    public array $medicineContext = [
        'group_id' => null,
        'level' => '',
        'leaflet' => '',
        'expiration_date' => null,
        'is_psychotropic' => false,
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function createProduct()
    {
        $this->reset(['productContext', 'medicineContext', 'editingProduct', 'isMedicine']);
        $this->productContext['status'] = true;
        Flux::modal('product-form')->show();
    }

    public function editProduct(Product $product)
    {
        $this->editingProduct = $product;
        $this->productContext = [
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'status' => $product->status,
        ];

        if ($product->medicine) {
            $this->isMedicine = true;
            $this->medicineContext = [
                'group_id' => $product->medicine->group_id,
                'level' => $product->medicine->level,
                'leaflet' => $product->medicine->leaflet,
                'expiration_date' => $product->medicine->expiration_date ? $product->medicine->expiration_date->format('Y-m-d') : null,
                'is_psychotropic' => $product->medicine->is_psychotropic,
            ];
        } else {
            $this->isMedicine = false;
            $this->reset('medicineContext');
        }

        Flux::modal('product-form')->show();
    }

    public function saveProduct()
    {
        $rules = [
            'productContext.name' => [
                'required',
                'string',
                'max:255',
                $this->editingProduct 
                    ? Rule::unique('products', 'name')->ignore($this->editingProduct->id) 
                    : Rule::unique('products', 'name'),
            ],
            'productContext.description' => 'nullable|string',
            'productContext.price' => 'required|numeric|min:0',
            'productContext.status' => 'boolean',
        ];

        if ($this->isMedicine) {
            $rules['medicineContext.group_id'] = 'required|exists:groups,id';
            $rules['medicineContext.level'] = 'nullable|string|max:50';
            $rules['medicineContext.leaflet'] = 'nullable|string';
            $rules['medicineContext.expiration_date'] = 'nullable|date';
            $rules['medicineContext.is_psychotropic'] = 'boolean';
        }

        $this->validate($rules);

        $productData = array_merge($this->productContext, [
            // Ensure status is boolean
            'status' => (bool) $this->productContext['status'],
        ]);

        if ($this->editingProduct) {
            $this->editingProduct->update($productData);
            $product = $this->editingProduct;
        } else {
            $product = Product::create($productData);
        }

        if ($this->isMedicine) {
            $medicineData = array_merge($this->medicineContext, [
                'is_psychotropic' => (bool) $this->medicineContext['is_psychotropic'],
            ]);

            if ($product->medicine) {
                $product->medicine()->update($medicineData);
            } else {
                $product->medicine()->create($medicineData);
            }
        } elseif ($this->editingProduct && $this->editingProduct->medicine) {
            // If it was a medicine but is no longer marked as one, delete the medicine record.
            $this->editingProduct->medicine()->delete();
        }

        Flux::modal('product-form')->close();
        $this->reset(['productContext', 'medicineContext', 'editingProduct', 'isMedicine']);
        $this->dispatch('notify', message: 'Producto guardado exitosamente.', type: 'success');
    }

    public function confirmDeactivate(Product $product)
    {
        $this->editingProduct = $product;
        Flux::modal('confirm-deactivation-product')->show();
    }

    public function deactivateProduct()
    {
        if ($this->editingProduct) {
            $this->editingProduct->delete(); // Soft delete
            Flux::modal('confirm-deactivation-product')->close();
            $this->reset(['editingProduct']);
            $this->dispatch('notify', message: 'Producto desactivado con éxito.', type: 'success');
        }
    }

    public function render()
    {
        $products = Product::search($this->search)
            ->query(fn ($query) => $query->with('medicine.group'))
            ->paginate(12);

        $groups = Cache::remember('groups_all', 86400, fn () => Group::orderBy('name')->get());

        return view('livewire.admin.product-manager', [
            'products' => $products,
            'groups' => $groups,
        ]);
    }
}
