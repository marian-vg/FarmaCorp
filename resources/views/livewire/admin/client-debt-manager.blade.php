<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <flux:heading size="xl">Saldos de Cuentas Corrientes (RF-16)</flux:heading>
            <flux:subheading>Monitoreo de deudas y saldos pendientes por cliente.</flux:subheading>
        </div>
        
        {{-- Resumen Financiero --}}
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4 rounded-xl">
            <flux:text size="sm" class="text-red-700 dark:text-red-400 uppercase font-bold">Total a Cobrar</flux:text>
            <flux:heading size="xl" class="text-red-600">${{ number_format($this->totalEnLaCalle, 2) }}</flux:heading>
        </div>
    </div>

    <div class="flex gap-4">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar cliente por nombre o teléfono..." class="flex-1" />
    </div>

    <div class="w-full overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Cliente</flux:table.column>
                <flux:table.column>Contacto</flux:table.column>
                <flux:table.column align="end">Saldo Pendiente (RF-12)</flux:table.column>
                <flux:table.column align="end">Acciones</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($clientes as $cliente)
                    <flux:table.row :key="'cliente-'.$cliente->id">
                        <flux:table.cell class="font-medium">
                            {{ $cliente->first_name }} {{ $cliente->last_name }}
                        </flux:table.cell>
                        
                        <flux:table.cell class="text-zinc-500">
                            {{ $cliente->phone ?: 'Sin teléfono' }}
                        </flux:table.cell>

                        <flux:table.cell align="end">
                            @if($cliente->saldo_pendiente > 0)
                                <flux:badge color="red" variant="solid" size="sm">
                                    ${{ number_format($cliente->saldo_pendiente, 2) }}
                                </flux:badge>
                            @else
                                <flux:badge color="green" variant="subtle" size="sm">Al día</flux:badge>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell align="end">
                            {{-- RF-24: Ver historial de compras/deudas [cite: 132] --}}
                            <flux:button icon="eye" size="xs" variant="ghost" />
                        </flux:button>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4" class="text-center py-12 text-zinc-500 italic">
                            No se encontraron clientes con los filtros aplicados.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <div class="mt-4">
        {{ $clientes->links() }}
    </div>
</div>