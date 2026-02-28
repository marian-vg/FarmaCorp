<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    {{-- Encabezado --}}
    <div class="flex justify-between items-center">
        <div>
            <flux:heading level="1" size="lg">Catálogo de Productos</flux:heading>
            <flux:subheading>Administra los artículos disponibles en FarmaCorp.</flux:subheading>
        </div>
        <flux:button icon="plus" variant="primary" wire:click="openCreateModal">Nuevo Producto</flux:button>
    </div>

    {{-- Tabs de Navegación --}}
    <div class="flex gap-4 border-b border-zinc-200 dark:border-zinc-700 mb-2">
        <button 
            wire:click="$set('tabActiva', 'gestion')" 
            class="px-4 py-2 font-medium text-sm {{ $tabActiva === 'gestion' ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'text-zinc-500' }}"
        >
            Productos Activos
        </button>
        <button 
            wire:click="$set('tabActiva', 'archivo')" 
            class="px-4 py-2 font-medium text-sm {{ $tabActiva === 'archivo' ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'text-zinc-500' }}"
        >
            Archivo / Inactivos
        </button>
    </div>

    {{-- Barra de Búsqueda --}}
    <div class="flex items-center gap-4 mb-2">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar por nombre o descripción..." class="flex-1" />
    </div>

    {{-- Tabla de Productos --}}
    <div class="w-full overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Producto</flux:table.column>
                <flux:table.column>Descripción</flux:table.column>
                <flux:table.column>Precio Base</flux:table.column>
                <flux:table.column>Estado</flux:table.column>
                <flux:table.column align="end">Acciones</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($this->products as $product)
                    <flux:table.row :key="$product->id">
                        <flux:table.cell class="font-medium">{{ $product->name }}</flux:table.cell>
                        <flux:table.cell class="text-zinc-500 text-xs">{{ str($product->description)->limit(50) ?: 'Sin descripción' }}</flux:table.cell>
                        <flux:table.cell class="font-mono text-indigo-600 dark:text-indigo-400">${{ number_format($product->price, 2) }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="{{ $product->status ? 'green' : 'zinc' }}" size="sm" inset="top bottom">
                                {{ $product->status ? 'En Venta' : 'Inactivo' }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex justify-end gap-2">
                                <flux:button size="sm" icon="pencil-square" variant="ghost" wire:click="editProduct({{ $product->id }})" />
                                
                                {{-- RF-02: Botón para Desactivar/Reactivar --}}
                                <flux:button 
                                    size="sm" 
                                    icon="{{ $product->status ? 'eye-slash' : 'arrow-path' }}" 
                                    variant="{{ $product->status ? 'danger' : 'success' }}" 
                                    ghost 
                                    wire:click="toggleStatus({{ $product->id }})" 
                                    title="{{ $product->status ? 'Desactivar' : 'Reactivar' }}"
                                />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center py-12 text-zinc-500">
                            No se encontraron productos en esta sección.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <div class="mt-4">
        {{ $this->products->links() }}
    </div>

    {{-- MODAL: Formulario de Producto (RF-01) --}}
    <flux:modal name="product-form" class="min-w-[30rem]">
        <form wire:submit="saveProduct" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingProduct ? 'Editar Producto' : 'Registrar Nuevo Producto' }}</flux:heading>
                <flux:subheading>Completa la información técnica y comercial del artículo.</flux:subheading>
            </div>

            <div class="space-y-4">
                <flux:input wire:model="productContext.name" label="Nombre del Producto" placeholder="Ej: Paracetamol 500mg x20" required />
                <flux:textarea wire:model="productContext.description" label="Descripción (Opcional)" placeholder="Detalles, marca o presentación..." />
                <flux:input wire:model="productContext.price" type="number" step="0.01" label="Precio de Venta ($)" icon="currency-dollar" required />
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">Guardar Producto</flux:button>
            </div>
        </form>
    </flux:modal>
</div>