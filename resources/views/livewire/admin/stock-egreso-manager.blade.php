<div class="p-6">
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <flux:heading size="xl" level="1">Egresos y Ajustes Operativos</flux:heading>
            <flux:subheading>Gestione el retiro físico de mercadería de los lotes activos (Ej: Mermas, Vencimientos, Roturas).</flux:subheading>
        </div>
        <div class="w-full sm:w-auto flex flex-col sm:flex-row items-center gap-2">
            <flux:select wire:model.live="filterGroup" placeholder="Categoría" class="w-48 min-w-48">
                <flux:select.option value="">Todas las categorías</flux:select.option>
                @foreach($groups as $g)
                    <flux:select.option value="{{ $g->id }}">{{ $g->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:input icon="magnifying-glass" wire:model.live.debounce.300ms="search" placeholder="Buscar lote por medicamento o número..." class="w-64" />
        </div>
    </div>

    <!-- Resultados de Búsqueda (Native HTML Table + Tailwind) -->
    <div class="bg-white dark:bg-zinc-900 shadow-sm sm:rounded-lg overflow-hidden border border-gray-200 dark:border-zinc-700">
        <div class="overflow-x-auto">
            <x-table>
                <x-table.head>
                    <x-table.heading>Medicamento</x-table.heading>
                    <x-table.heading>Lote</x-table.heading>
                    <x-table.heading>Vencimiento</x-table.heading>
                    <x-table.heading class="text-right">Stock Actual</x-table.heading>
                    <x-table.heading class="text-right">Restar</x-table.heading>
                </x-table.head>
                <x-table.body>
                    @forelse($batches as $batch)
                        <x-table.row>
                            <x-table.cell class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $batch->medicine?->product?->name ?? 'N/D' }}
                            </x-table.cell>
                            <x-table.cell class="text-sm text-zinc-500 dark:text-zinc-400">
                                <flux:badge variant="pill">{{ $batch->batch_number }}</flux:badge>
                            </x-table.cell>
                            <x-table.cell class="text-sm text-zinc-500 dark:text-zinc-400">
                                @if(Carbon\Carbon::parse($batch->expiration_date)->isPast())
                                    <span class="text-red-600 font-medium">{{ Carbon\Carbon::parse($batch->expiration_date)->format('d/m/Y') }} (Vencido)</span>
                                @else
                                    {{ Carbon\Carbon::parse($batch->expiration_date)->format('d/m/Y') }}
                                @endif
                            </x-table.cell>
                            <x-table.cell class="text-right text-sm font-medium {{ $batch->current_quantity <= $batch->minimum_stock ? 'text-orange-600' : 'text-zinc-900 dark:text-zinc-100' }}">
                                {{ $batch->current_quantity }} u.
                            </x-table.cell>
                            <x-table.cell class="text-right text-sm font-medium">
                                <flux:button size="sm" variant="danger" icon="minus" wire:click="selectBatch({{ $batch->id }}, {{ $batch->current_quantity }})">
                                    Retirar
                                </flux:button>
                            </x-table.cell>
                        </x-table.row>
                    @empty
                        <x-table.row>
                            <x-table.cell colspan="5" class="py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                No se encontraron lotes con stock activo.
                            </x-table.cell>
                        </x-table.row>
                    @endforelse
                </x-table.body>
            </x-table>
        </div>
        @if($batches->hasPages())
            <div class="bg-white dark:bg-zinc-900 px-4 py-3 border-t border-gray-200 dark:border-zinc-700 sm:px-6">
                {{ $batches->links() }}
            </div>
        @endif
    </div>

    <!-- Modal de Egreso Operativo -->
    <flux:modal name="egreso-modal" class="md:w-5/12">
        <form wire:submit="save" class="space-y-6">
            <flux:heading>Registrar Egreso/Ajuste</flux:heading>
            <flux:subheading>Retire unidades del lote. Stock Disponible actual: <strong class="text-zinc-900 dark:text-white">{{ $current_stock_display }}</strong></flux:subheading>
            
            <flux:input wire:model="quantity_to_remove" type="number" label="Cantidad a Retirar" min="1" max="{{ $current_stock_display }}" placeholder="Ej: 5" required autofocus />
            
            <flux:select wire:model="reason" label="Motivo de Egreso" placeholder="Seleccione un motivo..." required>
                <flux:select.option value="devolucion_proveedor">Devolución a Proveedor</flux:select.option>
                <flux:select.option value="merma_rotura">Merma / Rotura</flux:select.option>
                <flux:select.option value="robo">Robo / Extravío</flux:select.option>
                <flux:select.option value="destruccion_vencimiento">Destrucción por Vencimiento</flux:select.option>
            </flux:select>
            
            <div class="flex flex-col-reverse md:flex-row space-y-2 space-y-reverse md:space-y-0 md:space-x-2 justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost" class="w-full md:w-auto">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="danger" class="w-full md:w-auto">Confirmar Descuento</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
