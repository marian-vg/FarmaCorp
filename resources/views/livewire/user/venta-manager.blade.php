<div class="flex flex-col gap-6">
    {{-- Navegación por Tabs --}}
    <div class="flex gap-4 border-b border-zinc-200 dark:border-zinc-700">
        <button 
            wire:click="$set('tabActiva', 'vender')" 
            class="px-4 py-2 font-medium text-sm {{ $tabActiva === 'vender' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-zinc-500 hover:text-zinc-700' }}"
        >
            Nueva Venta
        </button>
        <button 
            wire:click="$set('tabActiva', 'historial')" 
            class="px-4 py-2 font-medium text-sm {{ $tabActiva === 'historial' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-zinc-500 hover:text-zinc-700' }}"
        >
            Historial de Ventas
        </button>
    </div>

    {{-- CONTENIDO: PESTAÑA NUEVA VENTA --}}
    @if($tabActiva === 'vender')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Lado Izquierdo: Catálogo de Productos --}}
            <div class="lg:col-span-2 space-y-4">
                
                {{-- RF-04: Bloqueo visual si no hay comprobante --}}
                <div class="{{ !$tipo_comprobante ? 'opacity-40 pointer-events-none' : '' }} transition-all duration-300">
                    <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar medicamento por nombre..." />
                    
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-4">
                        @foreach($products as $product)
    @php
        $stockActual = $product->stock?->cantidad_actual ?? 0;
        $stockMinimo = $product->stock?->stock_minimo ?? 0;
        $fueraDeStock = $stockActual <= 0;
    @endphp

    <flux:card 
        class="flex flex-col justify-between transition-all {{ $fueraDeStock ? 'opacity-60 grayscale' : 'hover:shadow-md cursor-pointer group' }}" 
        wire:click="{{ $fueraDeStock ? '' : 'agregarAlCarrito('.$product->id.')' }}"
    >
        <div>
            <div class="flex justify-between items-start">
                <flux:text size="xs" class="uppercase text-zinc-400">Medicamento</flux:text>
                
                {{-- Badge de Stock (Tu idea mejorada)  --}}
                @if($stockActual <= 0)
                    <flux:badge size="xs" color="red" variant="solid">Sin Stock</flux:badge>
                @elseif($stockActual <= $stockMinimo)
                    <flux:badge size="xs" color="yellow" variant="outline">Stock Bajo: {{ $stockActual }}</flux:badge>
                @else
                    <flux:badge size="xs" color="green" variant="subtle">{{ $stockActual }} disp.</flux:badge>
                @endif
            </div>
            <flux:heading size="sm">{{ $product->name }}</flux:heading>
        </div>

        <div class="mt-4 flex justify-between items-center">
            <flux:text class="font-bold text-indigo-600">${{ number_format($product->price, 2) }}</flux:text>
            
            @if(!$fueraDeStock)
                <flux:button size="xs" icon="plus" variant="subtle" class="group-hover:bg-indigo-50" />
            @else
                <flux:icon.x-mark class="text-red-400" />
            @endif
        </div>
    </flux:card>
@endforeach
                    </div>
                </div>

                @if(!$tipo_comprobante)
                    <div class="p-8 border-2 border-dashed border-zinc-200 rounded-xl text-center bg-zinc-50/30">
                        <flux:icon.document-text class="mx-auto h-12 w-12 text-zinc-300" />
                        <flux:heading class="mt-2 text-zinc-500">Selecciona un comprobante para habilitar la venta</flux:heading>
                        <flux:text size="sm">Esto es obligatorio según el requerimiento RF-04 del informe.</flux:text>
                    </div>
                @endif
            </div>

            {{-- Lado Derecho: Carrito / Resumen de Ticket --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 h-fit sticky top-4 space-y-6">
                
                {{-- CONFIGURACIÓN INICIAL (RF-04 y RF-22) --}}
                <div class="space-y-4 pb-4 border-b border-zinc-100 dark:border-zinc-800">
                    <flux:heading size="lg">Datos de Facturación</flux:heading>
                    
                    <flux:select wire:model.live="tipo_comprobante" label="Tipo de Comprobante (RF-04)" required>
                        <option value="">Seleccione el tipo...</option>
                        <option value="TICKET">Ticket Fiscal</option>
                        <option value="FACTURA-A">Factura A</option>
                        <option value="FACTURA-B">Factura B</option>
                    </flux:select>

                    <div class="relative">
                        <flux:input wire:model.live.debounce.300ms="search_cliente" label="Asociar Cliente (RF-22)" icon="user" placeholder="Nombre o teléfono..." />
                        
                        @if($search_cliente && $this->clientes->isNotEmpty() && !$cliente_id)
                            <div class="absolute z-10 w-full mt-1 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-lg overflow-hidden">
                                @foreach($this->clientes as $cli)
                                    <button wire:click="$set('cliente_id', {{ $cli->id }}); $set('search_cliente', '{{ $cli->first_name }} {{ $cli->last_name }}')" 
                                            class="w-full p-2 text-left text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 flex justify-between border-b last:border-0 border-zinc-100 dark:border-zinc-800">
                                        <span class="font-medium">{{ $cli->first_name }} {{ $cli->last_name }}</span>
                                        <span class="text-xs text-zinc-500">{{ $cli->phone }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        @if($cliente_id)
                            <div class="mt-2 flex items-center justify-between p-2 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md">
                                <flux:text size="sm" class="text-green-700 dark:text-green-400 font-medium">✓ Cliente vinculado</flux:text>
                                <button wire:click="$set('cliente_id', null); $set('search_cliente', '')" class="text-xs text-green-600 hover:underline">Quitar</button>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- EL CARRITO (RF-13) --}}
                <div class="space-y-3 max-h-64 overflow-y-auto">
                    <flux:heading size="sm" class="text-zinc-500 uppercase tracking-wider">Productos en Carrito</flux:heading>
                    @forelse($carrito as $item)
                        <div class="flex justify-between items-center text-sm border-b border-zinc-100 dark:border-zinc-800 pb-2">
                            <div class="flex-1">
                                <flux:text class="font-medium">{{ $item['name'] }}</flux:text>
                                <flux:text size="xs" class="text-zinc-500">{{ $item['cantidad'] }} x ${{ number_format($item['price'], 2) }}</flux:text>
                            </div>
                            <flux:button variant="ghost" icon="trash" size="xs" class="text-red-500" wire:click="quitarDelCarrito({{ $item['id'] }})" />
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-8 text-zinc-500 italic">
                            <flux:icon.shopping-cart class="w-8 h-8 mb-2 opacity-20" />
                            <flux:text size="sm">El carrito está vacío</flux:text>
                        </div>
                    @endforelse
                </div>

                {{-- TOTAL Y COBRO (RF-05) --}}
                <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4 space-y-4">
                    <div class="flex justify-between items-center">
                        <flux:heading size="md">TOTAL A PAGAR</flux:heading>
                        <flux:heading size="xl" class="text-indigo-600">${{ number_format($this->subtotal, 2) }}</flux:heading>
                    </div>

                    <div class="space-y-4 border-t pt-4">
    <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-zinc-200 dark:border-zinc-700">
        <div class="flex flex-col">
            <flux:text size="sm" class="font-medium text-zinc-900 dark:text-white">¿A Cuenta Corriente? (RF-11)</flux:text>
            <flux:text size="xs" class="text-zinc-500">Se registrará como deuda del cliente.</flux:text>
        </div>
        <flux:switch wire:model.live="es_cuenta_corriente" color="indigo" />
    </div>

    @if(!$es_cuenta_corriente)
        <flux:select wire:model="medio_pago_id" label="Medio de Pago" required>
            <option value="">Seleccione el método...</option>
            @foreach($mediosPago as $mp)
                <option value="{{ $mp->id }}">{{ $mp->nombre }}</option>
            @endforeach
        </flux:select>
    @else
        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <flux:text size="xs" class="text-blue-700 dark:text-blue-400">
                ℹ️ Esta venta se asociará al saldo de <strong>{{ $search_cliente ?: 'el cliente' }}</strong> sin ingreso de efectivo inmediato.
            </flux:text>
        </div>
    @endif

    <flux:button variant="primary" class="w-full" wire:click="procesarVenta" icon="banknotes" :disabled="!$tipo_comprobante || empty($carrito)">
        {{ $es_cuenta_corriente ? 'Registrar Deuda' : 'Confirmar y Facturar' }}
    </flux:button>
</div>
                </div>
            </div>
        </div>
    @endif

    {{-- CONTENIDO: PESTAÑA HISTORIAL DE VENTAS --}}
    @if($tabActiva === 'historial')
    <div class="space-y-4">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <flux:heading size="lg">Registro de Ventas Realizadas</flux:heading>
                <flux:subheading>Visualice y filtre los comprobantes emitidos.</flux:subheading>
            </div>
            
            <div class="flex items-center gap-3 w-full md:w-auto">
                <flux:select wire:model.live="filtroEstado" size="sm" class="w-48" placeholder="Todos los estados">
                    <flux:select.option value="">Todos los estados</flux:select.option>
                    <flux:select.option value="PAGADO">Solo Pagados</flux:select.option>
                    <flux:select.option value="PENDIENTE">Solo Cta. Corriente</flux:select.option>
                </flux:select>

                <flux:badge color="zinc" variant="outline" class="whitespace-nowrap">
                    Total: {{ $this->historialVentas->total() }}
                </flux:badge>
            </div>
        </div>
        
        <div class="w-full overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Fecha y Hora</flux:table.column>
                    <flux:table.column>Medio de Pago</flux:table.column>
                    <flux:table.column align="end">Monto Total</flux:table.column>
                    <flux:table.column align="end">Acciones</flux:table.column> {{-- Columna para el botón --}}
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($this->historialVentas as $venta)
                        <flux:table.row :key="'venta-'.$venta->id">
                            <flux:table.cell class="text-xs font-mono">
                                {{ $venta->fecha_emision->format('d/m/Y H:i:s') }}
                            </flux:table.cell>

                            <flux:table.cell>
                                @if($venta->estado === 'PENDIENTE')
                                    <flux:badge size="sm" color="red" variant="solid">Cuenta Corriente</flux:badge>
                                @else
                                    <flux:badge size="sm" color="zinc" variant="outline">
                                        {{ $venta->medioPago?->nombre ?? 'N/D' }}
                                    </flux:badge>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell align="end" class="font-bold text-indigo-600">
                                ${{ number_format($venta->total, 2) }}
                            </flux:table.cell>

                            {{-- El botón ahora está DENTRO de la fila --}}
                            <flux:table.cell align="end">
                                <flux:button 
                                    icon="information-circle" 
                                    size="xs" 
                                    variant="ghost" 
                                    wire:click="verDetalle({{ $venta->id }})" 
                                />
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4" class="text-center py-12 text-zinc-500 italic">
                                No se registran ventas en este período.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        <div class="mt-4">
            {{ $this->historialVentas->links() }}
        </div>
    </div>
@endif
    {{-- MODAL DETALLE DE VENTA --}}
<flux:modal name="detalle-venta-modal" class="md:w-5/12">
    <div class="space-y-6">
        @if($facturaSeleccionada)
            <div>
                <flux:heading size="lg">Detalle del Comprobante</flux:heading>
                <flux:subheading>
                    #{{ str_pad($facturaSeleccionada->id, 6, '0', STR_PAD_LEFT) }} 
                    ({{ $facturaSeleccionada->tipo_comprobante }})
                </flux:subheading>
            </div>

            <div class="space-y-4">
                <div class="border rounded-lg overflow-hidden border-zinc-200 dark:border-zinc-700">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-zinc-50 dark:bg-zinc-800 text-zinc-500 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-2">Producto</th>
                                <th class="px-4 py-2 text-center">Cant.</th>
                                <th class="px-4 py-2 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach($facturaSeleccionada->details as $detalle)
                                <tr>
                                    <td class="px-4 py-3 font-medium">{{ $detalle->product->name }}</td>
                                    <td class="px-4 py-3 text-center">{{ $detalle->cantidad }}</td>
                                    <td class="px-4 py-3 text-right">
                                        ${{ number_format($detalle->cantidad * $detalle->precio_unitario, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-between items-center p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl">
                    <flux:text class="font-bold">TOTAL</flux:text>
                    <flux:heading size="xl" class="text-indigo-600">
                        ${{ number_format($facturaSeleccionada->total, 2) }}
                    </flux:heading>
                </div>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-12">
                {{-- Spinner manual con Tailwind para evitar el error de Flux --}}
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mb-2"></div>
                <flux:text size="sm">Cargando detalles...</flux:text>
            </div>
        @endif

        <div class="flex justify-end">
            <flux:modal.close>
                <flux:button variant="ghost">Cerrar</flux:button>
            </flux:modal.close>
        </div>
    </div>
</flux:modal>
</div>