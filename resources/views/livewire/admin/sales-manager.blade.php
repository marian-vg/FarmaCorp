<div class="space-y-6">
    <flux:heading size="xl">Historial Global de Ventas</flux:heading>

    <div class="flex items-center gap-4">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar por responsable..." class="flex-1" />
    </div>

    <x-table>
        <x-table.head>
            <x-table.heading>Fecha</x-table.heading>
            <x-table.heading>Responsable</x-table.heading>
            <x-table.heading>Estado</x-table.heading>
            <x-table.heading class="text-right">Monto Total</x-table.heading>
            <x-table.heading class="text-right">Acciones</x-table.heading>
        </x-table.head>
        <x-table.body>
                @forelse($this->ventas as $venta)
                    <x-table.row wire:key="{{ $venta->id }}">
                        <x-table.cell class="text-xs font-mono">{{ $venta->fecha_emision->format('d/m/Y H:i') }}</x-table.cell>
                        <x-table.cell>{{ $venta->user->name }}</x-table.cell>
                        <x-table.cell><flux:badge color="green" size="sm">{{ $venta->estado }}</flux:badge></x-table.cell>
                        <x-table.cell class="text-right font-bold text-indigo-600">${{ number_format($venta->total, 2) }}</x-table.cell>
                        <x-table.cell class="text-right">
                            <flux:button size="xs" icon="eye" variant="ghost" wire:click="verDetalle({{ $venta->id }})" />
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="5" class="text-center">
                            <flux:text class="text-gray-500 dark:text-gray-400">No se encontraron ventas.</flux:text>
                        </x-table.cell>
                    </x-table.row>
                @endforelse
        </x-table.body>
    </x-table>
    {{ $this->ventas->links() }}

    {{-- Modal para ver los productos vendidos [cite: 525] --}}
    <flux:modal name="detalle-venta-modal" class="min-w-140">
        @if($ventaSeleccionada)
            <div class="space-y-4">
                <flux:heading size="lg">Detalle de Productos Vendidos</flux:heading>
                <flux:separator />
                
                <x-table class="mt-4 mb-4">
                    <x-table.head>
                        <x-table.heading>Producto</x-table.heading>
                        <x-table.heading>Cant.</x-table.heading>
                        <x-table.heading class="text-right">Unitario</x-table.heading>
                        <x-table.heading class="text-right">Subtotal</x-table.heading>
                    </x-table.head>
                    <x-table.body>
                            @foreach($ventaSeleccionada->details as $item)
                                <x-table.row>
                                    <x-table.cell>{{ $item->product->name }}</x-table.cell>
                                    <x-table.cell>{{ $item->cantidad }}</x-table.cell>
                                    <x-table.cell class="text-right">${{ number_format($item->precio_unitario, 2) }}</x-table.cell>
                                    <x-table.cell class="text-right font-medium">${{ number_format($item->cantidad * $item->precio_unitario, 2) }}</x-table.cell>
                                </x-table.row>
                            @endforeach
                    </x-table.body>
                </x-table>

                <div class="flex justify-between font-bold border-t pt-4">
                    <flux:text>TOTAL DE LA OPERACIÓN</flux:text>
                    <flux:heading size="md" class="text-indigo-600">${{ number_format($ventaSeleccionada->total, 2) }}</flux:heading>
                </div>
            </div>
        @endif
    </flux:modal>
</div>