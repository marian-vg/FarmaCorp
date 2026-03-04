<div class="p-6">
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <flux:heading size="xl" level="1">Ingreso de Mercadería</flux:heading>
            <flux:subheading>Busque un medicamento en el catálogo para registrar la entrada de un nuevo lote físico.</flux:subheading>
        </div>
        <div class="w-full sm:w-72">
            <flux:input icon="magnifying-glass" wire:model.live.debounce.300ms="search" placeholder="Buscar medicamento por nombre..." />
        </div>
    </div>

    <!-- Resultados de Búsqueda (Native HTML Table + Tailwind) -->
    <div class="bg-white dark:bg-zinc-900 shadow-sm sm:rounded-lg overflow-hidden border border-gray-200 dark:border-zinc-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                <thead class="bg-gray-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Medicamento</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Grupo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Precio Ref.</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-zinc-900 dark:divide-zinc-700">
                    @forelse($medicines as $medicine)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $medicine->product?->name ?? 'N/D' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                <flux:badge variant="pill">{{ $medicine->group?->name ?? 'Sin Grupo' }}</flux:badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                ${{ number_format($medicine->product?->price ?? 0, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <flux:button size="sm" variant="primary" icon="plus" wire:click="selectMedicine({{ $medicine->product_id }})">
                                    Ingresar Lote
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                No se encontraron medicamentos coincidentes con la búsqueda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
