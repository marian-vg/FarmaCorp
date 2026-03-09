<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="flex justify-between items-center">
        <flux:heading level="1" size="lg">Catálogo: Productos y Medicamentos</flux:heading>

        <div class="flex items-center gap-4">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar producto..." class="w-64" />
            <flux:button icon="plus" wire:click="createProduct" variant="primary">Nuevo Producto</flux:button>
        </div>
    </div>

    <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700">
        <x-table>
            <x-table.head>
                <x-table.heading>Nombre</x-table.heading>
                <x-table.heading>Tipo</x-table.heading>
                <x-table.heading>Precio</x-table.heading>
                <x-table.heading>Estado</x-table.heading>
                <x-table.heading class="text-right">Acciones</x-table.heading>
            </x-table.head>
            <x-table.body>
                @forelse($products as $product)
                    <x-table.row>
                        <x-table.cell>
                            <flux:text class="font-medium">{{ $product->medicine?->presentation_name ?: $product->name }}</flux:text>
                            @if($product->medicine && $product->medicine->group)
                                <div class="text-xs text-gray-500">{{ $product->medicine->group->name }}</div>
                            @endif
                        </x-table.cell>
                        <x-table.cell>
                            @if($product->medicine)
                                <flux:badge variant="primary" size="sm">Medicamento</flux:badge>
                                @if($product->medicine->is_psychotropic)
                                    <flux:badge variant="danger" size="sm">Psicotrópico</flux:badge>
                                @endif
                            @else
                                <flux:badge variant="solid" color="zinc" size="sm">Insumo/General</flux:badge>
                            @endif
                        </x-table.cell>
                        <x-table.cell>
                            @if($product->medicine)
                                <flux:text>${{ number_format($product->medicine->price, 2) }}</flux:text>
                            @else
                                <flux:text class="text-zinc-400 italic">N/D</flux:text>
                            @endif
                        </x-table.cell>
                        <x-table.cell>
                            @if($product->status)
                                <flux:badge variant="success" size="sm">Activo</flux:badge>
                            @else
                                <flux:badge variant="danger" size="sm">Inactivo</flux:badge>
                            @endif
                        </x-table.cell>
                        <x-table.cell class="text-right">
                            <div class="flex justify-end gap-2">
                                <flux:button size="sm" icon="pencil-square" variant="ghost" wire:click="editProduct({{ $product->id }})" />
                                <flux:button size="sm" icon="trash" variant="danger" ghost wire:click="confirmDeactivate({{ $product->id }})" />
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="5" class="text-center">
                            <flux:text class="text-gray-500 dark:text-gray-400">No se encontraron productos.</flux:text>
                        </x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table.body>
        </x-table>
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>

    <!-- Edit/Create Modal -->
    <flux:modal name="product-form" class="min-w-160">
        <form wire:submit="saveProduct" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingProduct ? 'Editar Producto' : 'Registrar Nuevo Producto' }}</flux:heading>
                <flux:subheading>Define las características del producto o insumo y si es aplicable como medicamento.</flux:subheading>
            </div>

            <!-- Basic product fields -->
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <flux:input wire:model="productContext.name" label="Nombre del producto" placeholder="Ej: Ibuprofeno" required />
                </div>
                <div class="col-span-2">
                    <flux:textarea wire:model="productContext.description" label="Descripción (Opcional)" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:input type="number" step="0.01" wire:model="productContext.price" label="Precio de venta unitario" required />
                    </div>
                    
                    {{-- RF-18: Vencimiento COMERCIAL --}}
                    <div>
                        <flux:input type="date" wire:model="productContext.price_expires_at" label="Vencimiento de la OFERTA/PRECIO" />
                        <flux:text size="xs" class="mt-1 text-orange-600 font-medium">Límite comercial del precio actual.</flux:text>
                    </div>
                </div>
                
                <div class="flex items-center mt-6">
                    <flux:switch wire:model="productContext.status" label="Producto en estado activo" />
                </div>
            </div>

            <flux:separator variant="subtle" />

            <!-- Medicine Toggle -->
            <div>
                <flux:switch wire:model.live="isMedicine" label="Es un Medicamento" description="Habilita esta opción si el producto requiere control médico, receta, fecha de vencimiento u hoja de información clínica." />
            </div>

            <!-- Medicine Fields shown conditionally -->
            @if($isMedicine)
                <div class="grid grid-cols-2 gap-4 bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-xl border border-zinc-200 dark:border-zinc-700">
                    <div class="col-span-2 sm:col-span-1">
                        <flux:select wire:model="medicineContext.group_id" label="Grupo Farmacológico" placeholder="Seleccione un grupo" required>
                            @foreach($groups as $group)
                                <flux:select.option value="{{ $group->id }}">{{ $group->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <flux:input wire:model="medicineContext.presentation_name" label="Nombre de Presentación (Opcional)" placeholder="Ej: Ibuprofeno 400mg x 10 comp." />
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <flux:input type="number" step="0.01" wire:model="medicineContext.price" label="Precio de venta unitario" placeholder="Ej: 450.00" required />
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <flux:input wire:model="medicineContext.level" label="Nivel/Dosificación (Opcional)" placeholder="Ej. 500mg, Jarabe infantil" />
                    </div>

                    <div class="col-span-2">
                        <flux:textarea wire:model="medicineContext.leaflet" label="Prospecto / Información Clínica (Opcional)" rows="3" />
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <flux:input type="date" wire:model="medicineContext.expiration_date" label="Vencimiento CLÍNICO (Medicamento)" />
                        <flux:text size="xs" class="mt-1 text-blue-600 font-medium">Fecha de caducidad del componente químico.</flux:text>
                    </div>

                    <div class="col-span-2 sm:col-span-1 flex items-center mt-6">
                        <flux:switch wire:model="medicineContext.is_psychotropic" label="Es Psicotrópico (Requiere receta archivable)" />
                    </div>
                </div>
            @endif

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Guardar Producto</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Delete Confirmation Modal -->
    <flux:modal name="confirm-deactivation-product" class="min-w-xs">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg" class="text-left text-red-600 dark:text-red-400">¿Desactivar producto?</flux:heading>
            </div>

            <flux:text class="text-left whitespace-normal">
                Esta acción marcará a <strong>{{ $editingProduct?->name }}</strong> como eliminado y ya no se mostrará en las búsquedas o inventarios regulares.
            </flux:text>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button wire:click="deactivateProduct" variant="danger">Eliminar (Soft Delete)</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
