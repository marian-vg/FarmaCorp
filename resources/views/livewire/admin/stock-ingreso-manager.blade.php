<div class="p-6">
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <flux:heading size="xl" level="1">Ingreso de Mercadería</flux:heading>
            <flux:subheading>Busque un medicamento en el catálogo para registrar la entrada de un nuevo lote físico.</flux:subheading>
        </div>
        <div class="w-full sm:w-auto flex flex-col sm:flex-row items-center gap-2">
            <flux:select wire:model.live="filterGroup" placeholder="Categoría" class="w-48 min-w-48">
                <flux:select.option value="">Todas las categorías</flux:select.option>
                @foreach($groups as $g)
                    <flux:select.option value="{{ $g->id }}">{{ $g->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:input icon="magnifying-glass" wire:model.live.debounce.300ms="search" placeholder="Buscar medicamento por nombre..." class="flex-1 min-w-[250px]">
                <x-slot name="append">
                    <div x-data x-show="$wire.search !== ''" style="display: none;" class="flex items-center pe-2">
                        <flux:button variant="subtle" size="sm" icon="x-mark" wire:click="$set('search', '')" class="h-6 w-6 px-0" />
                    </div>
                </x-slot>
            </flux:input>
        </div>
    </div>

    <!-- Resultados de Búsqueda (Native HTML Table + Tailwind) -->
    <div class="bg-white dark:bg-zinc-900 shadow-sm sm:rounded-lg overflow-hidden border border-gray-200 dark:border-zinc-700">
        <div class="overflow-x-auto">
            <x-table>
                <x-table.head>
                    <x-table.heading>Medicamento</x-table.heading>
                    <x-table.heading>Grupo</x-table.heading>
                    <x-table.heading>Precio Ref.</x-table.heading>
                    <x-table.heading class="text-right">Acciones</x-table.heading>
                </x-table.head>
                <x-table.body>
                    @forelse($medicines as $medicine)
                        <x-table.row>
                            <x-table.cell class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $medicine->presentation_name ?: ($medicine->product?->name ?? 'N/D') }}
                            </x-table.cell>
                            <x-table.cell class="text-sm text-zinc-500 dark:text-zinc-400">
                                <flux:badge variant="pill">{{ $medicine->group?->name ?? 'Sin Grupo' }}</flux:badge>
                            </x-table.cell>
                            <x-table.cell class="text-sm text-zinc-500 dark:text-zinc-400">
                                ${{ number_format($medicine->price ?? 0, 2) }}
                            </x-table.cell>
                            <x-table.cell class="text-right text-sm font-medium">
                                <flux:button size="sm" variant="primary" icon="plus" wire:click="selectMedicine({{ $medicine->id }})">
                                    Ingresar Lote
                                </flux:button>
                            </x-table.cell>
                        </x-table.row>
                    @empty
                        <x-table.row>
                            <x-table.cell colspan="4" class="py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                No se encontraron medicamentos coincidentes con la búsqueda.
                            </x-table.cell>
                        </x-table.row>
                    @endforelse
                </x-table.body>
            </x-table>
        </div>
        @if($medicines->hasPages())
            <div class="bg-white dark:bg-zinc-900 px-4 py-3 border-t border-gray-200 dark:border-zinc-700 sm:px-6">
                {{ $medicines->links() }}
            </div>
        @endif
    </div>

    <!-- Modal de Ingreso de Lote -->
    <flux:modal name="ingreso-modal" class="md:w-5/12">
        <form wire:submit="save" class="space-y-6">
            <flux:heading>Registrar Recepción de Lote</flux:heading>
            <flux:subheading>Por favor, complete los datos del lote que está ingresando al inventario físico.</flux:subheading>
            
            <flux:input wire:model="batch_number" label="Número de Lote" placeholder="Ej: LOTE-848" required autofocus />
            
            <flux:input wire:model="expiration_date" type="date" label="Fecha de Vencimiento" required />
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model="quantity_received" type="number" label="Cantidad Recibida" min="1" placeholder="Ej: 50" required />
                <flux:input wire:model="minimum_stock" type="number" label="Stock Mínimo (Alerta)" min="0" placeholder="Ej: 10" required />
            </div>

            <div class="flex flex-col-reverse md:flex-row space-y-2 space-y-reverse md:space-y-0 md:space-x-2 justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost" class="w-full md:w-auto">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" class="w-full md:w-auto">Confirmar Ingreso</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
