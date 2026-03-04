<div class="p-6">
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <flux:heading size="xl" level="1">Kardex de Movimientos</flux:heading>
            <flux:subheading>Registro histórico y auditable de todos los ingresos, egresos y ajustes del inventario.</flux:subheading>
        </div>
        <div class="w-full sm:w-72">
            <flux:input icon="magnifying-glass" wire:model.live.debounce.300ms="search" placeholder="Buscar por medicamento, lote o usuario..." />
        </div>
    </div>

    <!-- Resultados de Búsqueda (Native HTML Table + Tailwind) -->
    <div class="bg-white dark:bg-zinc-900 shadow-sm sm:rounded-lg overflow-hidden border border-gray-200 dark:border-zinc-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                <thead class="bg-gray-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Fecha y Hora</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Usuario</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Medicamento (Lote)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Razón</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Cantidad</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-zinc-900 dark:divide-zinc-700">
                    @forelse($movements as $movement)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $movement->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $movement->user?->name ?? 'Sistema' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                <span class="font-semibold">{{ $movement->batch?->medicine?->product?->name ?? 'N/D' }}</span>
                                <span class="text-zinc-500 dark:text-zinc-400 ml-1">({{ $movement->batch?->batch_number ?? 'N/D' }})</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($movement->type === 'ingreso')
                                    <flux:badge variant="success">Ingreso</flux:badge>
                                @else
                                    <flux:badge variant="danger">Egreso</flux:badge>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400 capitalize">
                                {{ str_replace('_', ' ', $movement->reason) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold {{ $movement->type === 'ingreso' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $movement->type === 'ingreso' ? '+' : '-' }}{{ $movement->quantity }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                No se encontraron movimientos de stock en el historial.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
            <div class="bg-white dark:bg-zinc-900 px-4 py-3 border-t border-gray-200 dark:border-zinc-700 sm:px-6">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</div>
