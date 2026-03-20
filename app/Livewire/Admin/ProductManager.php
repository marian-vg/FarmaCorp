<?php

namespace App\Livewire\Admin;

use App\Models\Group;
use App\Models\Product;
use App\Traits\Notifies;
use Flux\Flux;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Gestión de Productos y Medicamentos'])]
class ProductManager extends Component
{
    use Notifies, WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $filterGroup = '';

    public ?Product $editingProduct = null;

    public bool $isMedicine = false;

    public array $productContext = [
        'name' => '',
        'description' => '',
        'status' => true,
    ];

    public array $medicineContext = [
        'group_id' => null,
        'presentation_name' => '',
        'price' => null,
        'level' => '',
        'leaflet' => '',
        'expiration_date' => null,
        'is_psychotropic' => false,
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedFilterGroup()
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
            'status' => $product->status,
        ];

        if ($product->medicine) {
            $this->isMedicine = true;
            $this->medicineContext = [
                'group_id' => $product->medicine->group_id,
                'presentation_name' => $product->medicine->presentation_name,
                'price' => $product->medicine->price,
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
            'productContext.status' => 'boolean',
            'productContext.price_expires_at' => 'nullable|date',
        ];

        if ($this->isMedicine) {
            $rules['medicineContext.presentation_name'] = 'nullable|string|max:255';
            $rules['medicineContext.price'] = 'required|numeric|min:0';
            $rules['medicineContext.group_id'] = 'required|exists:groups,id';
            $rules['medicineContext.level'] = 'nullable|string|max:50';
            $rules['medicineContext.leaflet'] = 'nullable|string';
            $rules['medicineContext.expiration_date'] = 'nullable|date';
            $rules['medicineContext.is_psychotropic'] = 'boolean';
        }

        $this->validate($rules);

        $productData = [
            'name' => $this->productContext['name'],
            'description' => $this->productContext['description'],
            'status' => (bool) $this->productContext['status'],
        ];

        if (isset($this->productContext['price_expires_at'])) {
            $productData['price_expires_at'] = $this->productContext['price_expires_at'];
        }

        if (array_key_exists('price', $this->productContext)) {
            $productData['price'] = $this->productContext['price'];
        }

        if ($this->editingProduct) {
            if (isset($this->productContext['price']) && (float) $this->editingProduct->price !== (float) $this->productContext['price']) {
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
        $this->notify('Producto guardado exitosamente.', 'success');
    }

    public function confirmDeactivate(Product $product)
    {
        $this->editingProduct = $product;
        Flux::modal('confirm-deactivation-product')->show();
    }

    public function deactivateProduct()
    {
        if ($this->editingProduct) {
            $this->editingProduct->update(['status' => false]);
            Flux::modal('confirm-deactivation-product')->close();
            $this->reset(['editingProduct']);
            $this->notify('Producto desactivado con éxito.', 'success');
        }
    }

    public function reactivateProduct(Product $product)
    {
        $product->update(['status' => true]);
        $this->notify('Producto reactivado con éxito.', 'success');
    }

    public function render()
    {
        $products = Product::search($this->search)
            ->query(function ($query) {
                $query->with('medicine.group');

                if ($this->statusFilter !== '') {
                    $query->where('status', $this->statusFilter === '1');
                }

                if ($this->filterGroup !== '') {
                    $query->whereHas('medicine', function ($q) {
                        $q->where('group_id', $this->filterGroup);
                    });
                }
            })
            ->paginate(12);

        $groups = Cache::remember('groups_all', 86400, fn () => Group::orderBy('name')->get());

        return view('livewire.admin.product-manager', [
            'products' => $products,
            'groups' => $groups,
        ]);
    }
}
