<div class="space-y-6">
    <div class="flex justify-between items-center">
        <flux:heading size="xl">Historial Global de Ventas</flux:heading>
        <flux:button variant="ghost" icon="arrow-path" wire:click="limpiarFiltros">Limpiar Filtros</flux:button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm">
        {{-- Filtro Cliente --}}
        <flux:select wire:model.live="filtroCliente" label="Cliente" placeholder="Todos los clientes">
            <option value="">Todos los clientes</option>
            @foreach($this->clientes as $cli)
                <option value="{{ $cli->id }}">{{ $cli->first_name }} {{ $cli->last_name }}</option>
            @endforeach
        </flux:select>

        {{-- Filtro Tipo --}}
        <flux:select wire:model.live="filtroTipo" label="Comprobante">
            <option value="">Todos los tipos</option>
            <option value="TICKET">Ticket Fiscal</option>
            <option value="FACTURA-A">Factura A</option>
            <option value="FACTURA-B">Factura B</option>
        </flux:select>

        {{-- Filtro Fecha Desde --}}
        <flux:input type="date" wire:model.live="fechaInicio" label="Desde" />

        {{-- Filtro Fecha Hasta --}}
        <flux:input type="date" wire:model.live="fechaFin" label="Hasta" />
    </div>

    {{-- Buscador por Responsable (El que ya tenías) --}}
    <div class="flex items-center gap-4">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar por vendedor..." class="flex-1" />
    </div>

    <div class="w-full overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
        <x-table>
            <x-table.head>
                <x-table.heading>Fecha</x-table.heading>
                <x-table.heading>Responsable</x-table.heading>
                <x-table.heading>Cliente</x-table.heading>
                <x-table.heading>Estado</x-table.heading>
                <x-table.heading class="text-right">Monto Total</x-table.heading>
                <x-table.heading class="text-right">Acciones</x-table.heading>
            </x-table.head>

            <x-table.body>
                @forelse($this->ventas as $venta)
                    <x-table.row :key="$venta->id">
                        <x-table.cell class="text-xs font-mono">{{ $venta->fecha_emision->format('d/m/Y H:i') }}</x-table.cell>
                        <x-table.cell>
                            <div class="flex flex-col">
                                <span class="font-medium">{{ $venta->user->name }}</span>
                                <span class="text-[10px] text-zinc-500 uppercase">Vendedor</span>
                            </div>
                        </x-table.cell>
                        
                        <x-table.cell>
                            <span class="text-sm {{ $venta->cliente ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-400 italic' }}">
                                {{ $venta->cliente ? ($venta->cliente->first_name . ' ' . $venta->cliente->last_name) : 'Consumidor Final' }}
                            </span>
                        </x-table.cell>

                        <x-table.cell>
                            <flux:badge :color="$venta->estado === 'PENDIENTE' ? 'yellow' : 'green'" size="sm" inset="top bottom">
                                {{ $venta->estado }}
                            </flux:badge>
                        </x-table.cell>

                        <x-table.cell class="text-right font-bold text-indigo-600">${{ number_format($venta->total, 2) }}</x-table.cell>
                        
                        <x-table.cell class="text-right">
                            <div class="flex gap-2 justify-end">
                                <flux:button size="xs" icon="eye" variant="ghost" wire:click="verDetalle({{ $venta->id }})" tooltip="Auditar" />
                                <flux:button size="xs" icon="document-arrow-down" variant="ghost" class="text-indigo-600" wire:click="descargarFactura({{ $venta->id }})" tooltip="Descargar PDF" />
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="6" class="text-center py-10 italic text-zinc-400">
                            No hay comprobantes de venta registrados con los filtros aplicados.
                        </x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table.body>
        </x-table>
    </div>
    {{ $this->ventas->links() }}

    {{-- Modal para ver los productos vendidos [cite: 525] --}}
    <flux:modal name="detalle-venta-modal" class="min-w-[40rem]">
        @if($ventaSeleccionada)
            <div class="space-y-6">
                <div class="flex justify-between items-start">
                    <div>
                        <flux:heading size="lg">Auditoría de Comprobante #{{ str_pad($ventaSeleccionada->id, 6, '0', STR_PAD_LEFT) }}</flux:heading>
                        <flux:subheading>{{ $ventaSeleccionada->tipo_comprobante }} | Vendedor: {{ $ventaSeleccionada->user->name }}</flux:subheading>
                    </div>
                    <flux:badge :color="$ventaSeleccionada->estado === 'PENDIENTE' ? 'yellow' : 'green'" variant="solid">
                        {{ $ventaSeleccionada->estado }}
                    </flux:badge>
                </div>

                <flux:separator />
                
                <x-table class="mt-4 mb-4">
                    <x-table.head>
                        <x-table.heading>Producto</x-table.heading>
                        <x-table.heading>Cant.</x-table.heading>
                        <x-table.heading class="text-right">Unitario</x-table.heading>
                        <x-table.heading class="text-right">Subtotal</x-table.heading>
                    </x-table.head>
                    <x-table.body>
                            @forelse($ventaSeleccionada->details as $item)
                                <x-table.row>
                                    <x-table.cell>{{ $item->product->name }}</x-table.cell>
                                    <x-table.cell>{{ $item->cantidad }}</x-table.cell>
                                    <x-table.cell class="text-right">${{ number_format($item->precio_unitario, 2) }}</x-table.cell>
                                    <x-table.cell class="text-right font-medium">${{ number_format($item->cantidad * $item->precio_unitario, 2) }}</x-table.cell>
                                </x-table.row>
                            @empty
                                <x-table.row>
                                    <x-table.cell colspan="4" class="text-center text-zinc-500 italic py-6">
                                        <flux:icon.inbox class="w-8 h-8 opacity-20 mx-auto mb-2" />
                                        No hay detalles registrados en este comprobante.
                                    </x-table.cell>
                                </x-table.row>
                            @endforelse
                    </x-table.body>
                </x-table>

                @if($ventaSeleccionada->pagos->isNotEmpty())
                    <div class="space-y-2">
                        <flux:heading size="sm" class="text-zinc-500 uppercase tracking-wider">Flujo de Fondos (Pagos)</flux:heading>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($ventaSeleccionada->pagos->groupBy('id_medio_pago') as $idMedio => $grupoPagos)
                                <div class="flex justify-between items-center text-xs p-3 bg-zinc-50 dark:bg-zinc-800 border rounded-xl">
                                    <span class="font-medium">{{ $grupoPagos->first()->medioPago->nombre }}</span>
                                    <span class="font-bold text-green-600">${{ number_format($grupoPagos->sum('monto'), 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Resumen Financiero Consolidado --}}
                <div class="space-y-3 p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200">
                    <div class="flex justify-between text-sm text-zinc-500 italic">
                        <span>Subtotal Neto:</span>
                        <span>${{ number_format($ventaSeleccionada->total - $ventaSeleccionada->ajuste_global, 2) }}</span>
                    </div>
                    @if($ventaSeleccionada->ajuste_global != 0)
                        <div class="flex justify-between text-sm {{ $ventaSeleccionada->ajuste_global < 0 ? 'text-green-600' : 'text-orange-600' }}">
                            <span>Ajuste (Desc/Rec):</span>
                            <span class="font-medium">${{ number_format($ventaSeleccionada->ajuste_global, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between items-center py-2 border-t font-bold">
                        <flux:text class="uppercase">Total Facturado:</flux:text>
                        <flux:heading size="lg" class="text-indigo-600">${{ number_format($ventaSeleccionada->total, 2) }}</flux:heading>
                    </div>

                    @php 
                        $pagado = $ventaSeleccionada->pagos->sum('monto'); 
                        $pendiente = $ventaSeleccionada->total - $pagado;
                    @endphp

                    <div class="flex justify-between text-sm text-green-600 font-medium">
                        <span>Total Percibido en Caja:</span>
                        <span>${{ number_format($pagado, 2) }}</span>
                    </div>

                    @if($pendiente > 0.01)
                        <div class="flex justify-between items-center p-2 mt-2 bg-red-50 dark:bg-red-900/20 border border-red-100 rounded-lg font-bold text-red-700 uppercase">
                            <span>Saldo Pendiente (Deuda Cliente):</span>
                            <span>${{ number_format($pendiente, 2) }}</span>
                        </div>
                    @endif
                </div>

                <div class="flex justify-end gap-2">
                    <flux:modal.close><flux:button variant="ghost">Cerrar</flux:button></flux:modal.close>
                    <flux:button variant="primary" icon="document-arrow-down" wire:click="descargarFactura({{ $ventaSeleccionada->id }})">Descargar PDF</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>