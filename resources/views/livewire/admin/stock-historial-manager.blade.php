<div class="p-6">
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <flux:heading size="xl" level="1">Kardex de Movimientos</flux:heading>
            <flux:subheading>Registro histórico y auditable de todos los ingresos, egresos y ajustes del inventario.</flux:subheading>
        </div>
        <div class="w-full sm:w-auto flex flex-col sm:flex-row items-center gap-2">
            <flux:input type="date" wire:model.live="fecha_desde" aria-label="Desde fecha" />
            <flux:input type="date" wire:model.live="fecha_hasta" aria-label="Hasta fecha" />
            <flux:select wire:model.live="filterType" class="w-40 min-w-40">
                <flux:select.option value="">Tipo (Todos)</flux:select.option>
                <flux:select.option value="ingreso">Ingresos</flux:select.option>
                <flux:select.option value="egreso">Egresos</flux:select.option>
            </flux:select>
            
            <flux:input icon="magnifying-glass" wire:model.live.debounce.300ms="search" placeholder="Buscar movimientos..." />
        </div>
    </div>

    <!-- Resultados de Búsqueda (Native HTML Table + Tailwind) -->
    <div class="bg-white dark:bg-zinc-900 shadow-sm sm:rounded-lg overflow-hidden border border-gray-200 dark:border-zinc-700">
        <div class="overflow-x-auto">
            <x-table>
                <x-table.head>
                    <x-table.heading>Fecha y Hora</x-table.heading>
                    <x-table.heading>Usuario</x-table.heading>
                    <x-table.heading>Medicamento (Lote)</x-table.heading>
                    <x-table.heading>Tipo</x-table.heading>
                    <x-table.heading>Razón</x-table.heading>
                    <x-table.heading class="text-right">Cantidad</x-table.heading>
                </x-table.head>
                <x-table.body>
                    @forelse($movements as $movement)
                        <x-table.row>
                            <x-table.cell class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $movement->created_at->format('d/m/Y H:i:s') }}
                            </x-table.cell>
                            <x-table.cell class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $movement->user?->name ?? 'Sistema' }}
                            </x-table.cell>
                            <x-table.cell class="text-sm text-zinc-900 dark:text-zinc-100">
                                <span class="font-semibold">{{ $movement->batch?->medicine?->presentation_name ?? 'N/D' }}</span>
                                <span class="text-zinc-500 dark:text-zinc-400 ml-1">({{ $movement->batch?->batch_number ?? 'N/D' }})</span>
                            </x-table.cell>
                            <x-table.cell class="text-sm">
                                @if($movement->type === 'ingreso')
                                    <flux:badge variant="success">Ingreso</flux:badge>
                                @else
                                    <flux:badge variant="danger">Egreso</flux:badge>
                                @endif
                            </x-table.cell>
                            <x-table.cell class="text-sm text-zinc-500 dark:text-zinc-400 capitalize">
                                {{ str_replace('_', ' ', $movement->reason) }}
                            </x-table.cell>
                            <x-table.cell class="text-right text-sm font-bold {{ $movement->type === 'ingreso' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $movement->type === 'ingreso' ? '+' : '-' }}{{ $movement->quantity }}
                            </x-table.cell>
                        </x-table.row>
                    @empty
                        <x-table.row>
                            <x-table.cell colspan="6" class="py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                No se encontraron movimientos de stock en el historial.
                            </x-table.cell>
                        </x-table.row>
                    @endforelse
                </x-table.body>
            </x-table>
        </div>
        @if($movements->hasPages())
            <div class="bg-white dark:bg-zinc-900 px-4 py-3 border-t border-gray-200 dark:border-zinc-700 sm:px-6">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</div>
