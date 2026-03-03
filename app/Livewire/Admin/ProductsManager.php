<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Flux\Flux;

#[Layout('components.layouts.app', ['title' => 'Gestión de Productos'])]
class ProductsManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $tabActiva = 'gestion'; // 'gestion' o 'archivo'
    public ?Product $editingProduct = null;

    // Contexto para el formulario de creación/edición
    public array $productContext = [
        'name' => '',
        'description' => '',
        'price' => '',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->reset(['productContext', 'editingProduct']);
        Flux::modal('product-form')->show();
    }

    public function editProduct(Product $product)
    {
        $this->editingProduct = $product;
        $this->productContext = [
            'name' => $product->name,
            'description' => $product->description ?? '',
            'price' => $product->price,
        ];
        Flux::modal('product-form')->show();
    }

    public function saveProduct()
    {
        $this->validate([
            'productContext.name' => 'required|string|max:255',
            'productContext.description' => 'nullable|string|max:500',
            'productContext.price' => 'required|numeric|min:0',
        ], [], [
            'productContext.name' => 'nombre',
            'productContext.price' => 'precio',
        ]);

        if ($this->editingProduct) {
            $this->editingProduct->update($this->productContext);
            Flux::toast('Producto actualizado correctamente.', variant: 'success');
        } else {
            Product::create([
                'name' => $this->productContext['name'],
                'description' => $this->productContext['description'],
                'price' => $this->productContext['price'],
                'status' => true,
            ]);
            Flux::toast('Producto registrado con éxito.', variant: 'success');
        }

        Flux::modal('product-form')->close();
        $this->reset(['productContext', 'editingProduct']);
    }

    // Lógica para Desactivar/Activar (RF-02)
    public function toggleStatus(Product $product)
    {
        $newStatus = !$product->status;
        $product->update(['status' => $newStatus]);
        
        $msg = $newStatus ? 'Producto reactivado.' : 'Producto desactivado.';
        Flux::toast($msg, variant: $newStatus ? 'success' : 'warning');
    }

    #[Computed]
    public function products()
    {
        return Product::query()
            ->when($this->tabActiva === 'gestion', fn($q) => $q->where('status', true))
            ->when($this->tabActiva === 'archivo', fn($q) => $q->where('status', false))
            ->when($this->search, function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name', 'asc')
            ->paginate(12);
    }

    public function render()
    {
        return view('livewire.admin.products-manager');
    }
}