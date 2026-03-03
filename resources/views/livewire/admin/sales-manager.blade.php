<div class="space-y-6">
    <flux:heading size="xl">Historial Global de Ventas</flux:heading>

    <div class="flex items-center gap-4">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar por responsable..." class="flex-1" />
    </div>

    <div class="w-full overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Fecha</flux:table.column>
                <flux:table.column>Responsable</flux:table.column>
                <flux:table.column>Estado</flux:table.column>
                <flux:table.column align="end">Monto Total</flux:table.column>
                <flux:table.column align="end">Acciones</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach($this->ventas as $venta)
                    <flux:table.row :key="$venta->id">
                        <flux:table.cell class="text-xs font-mono">{{ $venta->fecha_emision->format('d/m/Y H:i') }}</flux:table.cell>
                        <flux:table.cell>{{ $venta->user->name }}</flux:table.cell>
                        <flux:table.cell><flux:badge color="green" size="sm">{{ $venta->estado }}</flux:badge></flux:table.cell>
                        <flux:table.cell align="end" class="font-bold text-indigo-600">${{ number_format($venta->total, 2) }}</flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:button size="xs" icon="eye" variant="ghost" wire:click="verDetalle({{ $venta->id }})" />
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
    {{ $this->ventas->links() }}

    {{-- Modal para ver los productos vendidos [cite: 525] --}}
    <flux:modal name="detalle-venta-modal" class="min-w-[35rem]">
        @if($ventaSeleccionada)
            <div class="space-y-4">
                <flux:heading size="lg">Detalle de Productos Vendidos</flux:heading>
                <flux:separator />
                
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Producto</flux:table.column>
                        <flux:table.column>Cant.</flux:table.column>
                        <flux:table.column align="end">Unitario</flux:table.column>
                        <flux:table.column align="end">Subtotal</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($ventaSeleccionada->details as $item)
                            <flux:table.row>
                                <flux:table.cell>{{ $item->product->name }}</flux:table.cell>
                                <flux:table.cell>{{ $item->cantidad }}</flux:table.cell>
                                <flux:table.cell align="end">${{ number_format($item->precio_unitario, 2) }}</flux:table.cell>
                                <flux:table.cell align="end" class="font-medium">${{ number_format($item->cantidad * $item->precio_unitario, 2) }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>

                <div class="flex justify-between font-bold border-t pt-4">
                    <flux:text>TOTAL DE LA OPERACIÓN</flux:text>
                    <flux:heading size="md" class="text-indigo-600">${{ number_format($ventaSeleccionada->total, 2) }}</flux:heading>
                </div>
            </div>
        @endif
    </flux:modal>
</div>