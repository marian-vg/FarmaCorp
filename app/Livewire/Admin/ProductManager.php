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
                'required', 'string', 'max:255',
                $this->editingProduct 
                    ? Rule::unique('products', 'name')->ignore($this->editingProduct->id) 
                    : Rule::unique('products', 'name'),
            ],
            'productContext.description' => 'nullable|string',
            'productContext.price' => 'required|numeric|min:0',
            'productContext.status' => 'boolean',
            'productContext.price_expires_at' => 'nullable|date', // Agregamos validación para RF-18
        ];

        if ($this->isMedicine) {
            $rules['medicineContext.group_id'] = 'required|exists:groups,id';
            $rules['medicineContext.level'] = 'nullable|string|max:50';
            $rules['medicineContext.leaflet'] = 'nullable|string';
            $rules['medicineContext.expiration_date'] = 'nullable|date';
            $rules['medicineContext.is_psychotropic'] = 'boolean';
        }

        $this->validate($rules);

        // 1. Manejo del Producto y Fecha de Actualización (RF-17)

        $productData = [
            'name' => $this->productContext['name'],
            'description' => $this->productContext['description'],
            'price' => $this->productContext['price'],
            'status' => (bool) $this->productContext['status'],
            'price_expires_at' => $this->productContext['price_expires_at'],
        ];

        if ($this->editingProduct) {
            if ((float)$this->editingProduct->price !== (float)$this->productContext['price']) {
                $productData['price_updated_at'] = now();
            }
            $this->editingProduct->update($productData);
            $product = $this->editingProduct;
        } else {
            $productData['price_updated_at'] = now();
            $product = Product::create($productData);
        }

        // 2. Manejo de la extensión de Medicina
        if ($this->isMedicine) {
            if ($product->medicine) {
                $product->medicine()->update($this->medicineContext);
            } else {
                $product->medicine()->create($this->medicineContext);
            }
        } elseif ($this->editingProduct && $this->editingProduct->medicine) {
            $this->editingProduct->medicine()->delete();
            }

        Flux::modal('product-form')->close();
        $this->reset(['productContext', 'medicineContext', 'editingProduct', 'isMedicine']);
        $this->dispatch('notify', message: 'Producto guardado con éxito.');
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
        }
    }

    public function render()
    {
        $products = Product::search($this->search)
            ->query(fn ($query) => $query->with('medicine.group'))
            ->paginate(12);

        $groups = Group::orderBy('name')->get();

        return view('livewire.admin.product-manager', [
            'products' => $products,
            'groups' => $groups,
        ]);
    }
}
