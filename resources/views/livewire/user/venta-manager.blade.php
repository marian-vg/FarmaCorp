<div class="flex flex-col gap-6">
    <div class="flex gap-4 border-b border-zinc-200 dark:border-zinc-700">
        <button wire:click="$set('tabActiva', 'vender')" 
            class="px-4 py-2 font-medium text-sm {{ $tabActiva === 'vender' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-zinc-500 hover:text-zinc-700' }}">
            Nueva Venta
        </button>
        <button wire:click="$set('tabActiva', 'historial')" 
            class="px-4 py-2 font-medium text-sm {{ $tabActiva === 'historial' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-zinc-500 hover:text-zinc-700' }}">
            Historial de Ventas
        </button>
    </div>

    @if($tabActiva === 'vender')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 space-y-4">
                <div class="{{ !$tipo_comprobante ? 'opacity-40 pointer-events-none' : '' }} transition-all duration-300">
                    <div class="flex gap-2">
                        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar medicamento..." class="flex-1" />
                        <flux:select wire:model.live="filterGroup" placeholder="Categoría" class="w-48 min-w-48">
                            <flux:select.option value="">Todas las categorías</flux:select.option>
                            @foreach($groups as $g)
                                <flux:select.option value="{{ $g->id }}">{{ $g->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-4">
                        @forelse($medicines as $medicine)
                            @php
                                // Resta dinámica "En Vivo" Fase 10
                                $cantidadEnCarrito = isset($carrito[$medicine->id]) ? $carrito[$medicine->id]['cantidad'] : 0;
                                $stockRealDB = $medicine->stock?->cantidad_actual ?? 0;
                                $stockActual = max(0, $stockRealDB - $cantidadEnCarrito);
                                
                                $fueraDeStock = $stockActual <= 0;
                                $product = $medicine->product;

                                $precioVencido = $product->price_expires_at && $product->price_expires_at->isPast();
                                
                                $ultimoCambio = $product->price_updated_at ?: $product->created_at;
                                $diasAntiguedad = (int) $ultimoCambio->diffInDays(now());
                                $precioAntiguo = $diasAntiguedad > $maxDays;

                                $bloqueadoParaVenta = $fueraDeStock || $precioVencido || $precioAntiguo;
                            @endphp

                            <flux:card 
                                class="flex flex-col justify-between transition-all {{ $bloqueadoParaVenta ? 'opacity-60 grayscale' : 'hover:shadow-md cursor-pointer group' }}" 
                                wire:click="{{ $bloqueadoParaVenta ? '' : 'agregarAlCarrito('.$medicine->id.')' }}"
                            >
                                <div>
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex flex-col gap-1">
                                            <flux:text size="xs" class="uppercase text-zinc-400 font-semibold tracking-wider">Medicamento</flux:text>
                                            
                                            @if($precioVencido)
                                                <flux:badge size="xs" color="red" variant="solid">Precio Caducado</flux:badge>
                                            @elseif($precioAntiguo)
                                                <flux:badge size="xs" color="orange" variant="subtle" icon="clock">Revisar Precio</flux:badge>
                                            @endif
                                        </div>
                                        
                                        @if($medicine->leaflet)
                                            <button 
                                                wire:click.stop="viewLeaflet({{ $product->id }})" 
                                                class="p-1 rounded-full hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-400 hover:text-indigo-600 transition-colors"
                                                title="Ver Prospecto"
                                            >
                                                <flux:icon.information-circle variant="micro" />
                                            </button>
                                        @endif
                                    </div>

                                    <div class="space-y-1">
                                        <flux:heading size="sm" class="leading-tight">{{ $medicine->presentation_name ?: $product->name }}</flux:heading>
                                        
                                        <div class="flex items-center gap-1.5">
                                            @if($fueraDeStock && $stockRealDB > 0)
                                                <flux:badge size="xs" color="orange" variant="solid">Lim. Carrito</flux:badge>
                                            @elseif($fueraDeStock)
                                                <flux:badge size="xs" color="red" variant="solid">Sin Stock</flux:badge>
                                            @else
                                                <flux:badge size="xs" :color="$stockActual <= ($medicine->stock?->stock_minimo ?? 5) ? 'yellow' : 'green'" variant="subtle">
                                                    {{ $stockActual }} disponibles
                                                </flux:badge>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 flex justify-between items-center pt-2 border-t border-zinc-50 dark:border-zinc-800/50">
                                    <div class="flex flex-col">
                                        <flux:text class="font-bold text-indigo-600">${{ number_format($medicine->price, 2) }}</flux:text>
                                        @if($precioAntiguo)
                                            <span class="text-[9px] text-zinc-400">Act. hace {{ $diasAntiguedad }} d</span>
                                        @endif
                                    </div>
                                    
                                    @if(!$bloqueadoParaVenta)
                                        <div class="flex gap-1">
                                            <flux:button size="xs" icon="plus" variant="subtle" class="group-hover:bg-indigo-50" wire:click.stop="agregarAlCarrito({{ $medicine->id }})" tooltip="Agregar 1 unidad" />
                                            <flux:button size="xs" icon="squares-plus" variant="ghost" class="group-hover:text-indigo-600" wire:click.stop="openCustomModal({{ $medicine->id }}, 'agregar')" tooltip="Agregar cantidad personalizada" />
                                        </div>
                                    @else
                                        <flux:icon.x-mark class="text-red-400 w-4 h-4" />
                                    @endif
                                </div>
                            </flux:card>
                        @empty
                            <div class="col-span-full flex flex-col items-center justify-center py-16 text-zinc-400 dark:text-zinc-500">
                                <flux:icon.magnifying-glass class="w-12 h-12 mb-3 opacity-20" />
                                <flux:text size="sm">No se encontraron productos coincidentes o no existen registros.</flux:text>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Lado Derecho: Carrito --}}
            <div wire:key="resumen-venta-{{ count($carrito) }}-{{ count($pagos_realizados) }}"
                class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 h-fit max-h-[calc(100vh-2rem)] overflow-y-auto flex flex-col sticky top-4 space-y-6 scrollbar-thin scrollbar-thumb-zinc-200 dark:scrollbar-thumb-zinc-700">
                
                @if(!$this->cajaActiva)
                    <div class="flex flex-col items-center justify-center py-10 text-center space-y-4">
                        <flux:icon.lock-closed class="text-orange-600 w-12 h-12 opacity-50" />
                        <flux:heading size="md">Caja Cerrada</flux:heading>
                        <flux:text size="sm">Debes abrir un turno para facturar.</flux:text>
                        <flux:button variant="primary" size="sm" href="{{ route('dashboard') }}">Ir a Cajas</flux:button>
                    </div>
                @else
                    {{-- Datos de Facturación --}}
                    <div class="space-y-4 pb-4 border-b border-zinc-100 dark:border-zinc-800">
                        <flux:select wire:model.live="tipo_comprobante" label="Comprobante (RF-04)" required>
                            <option value="">Seleccione...</option>
                            <option value="TICKET">Ticket Fiscal</option>
                            <option value="FACTURA-A">Factura A</option>
                            <option value="FACTURA-B">Factura B</option>
                        </flux:select>

                        <div class="relative">
                            <flux:input wire:model.live.debounce.300ms="search_cliente" label="Cliente" icon="user" placeholder="Buscar..." />
                            @if($search_cliente && $this->clientes->isNotEmpty() && !$cliente_id)
                                <div class="absolute z-10 w-full mt-1 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-lg overflow-hidden">
                                    @foreach($this->clientes as $cli)
                                        <button 
                                            wire:click="$set('cliente_id', {{ $cli->id }}); $set('search_cliente', '{{ $cli->first_name }} {{ $cli->last_name }}')" 
                                            class="w-full p-3 text-left hover:bg-zinc-50 dark:hover:bg-zinc-700/50 flex flex-col border-b last:border-0 border-zinc-100 dark:border-zinc-800"
                                        >
                                            {{-- Forzamos el color del texto para que sea visible --}}
                                            <span class="font-bold text-zinc-900 dark:text-white">{{ $cli->first_name }} {{ $cli->last_name }}</span>
                                            <span class="text-xs text-zinc-500">{{ $cli->phone ?: 'Sin teléfono' }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                            @if($cliente_id)
                                <div class="mt-2 p-2 bg-green-50 border border-green-200 rounded-md flex justify-between items-center">
                                    <flux:text size="sm" class="text-green-700">✓ Cliente vinculado</flux:text>
                                    <button wire:click="$set('cliente_id', null); $set('search_cliente', '')" class="text-xs text-green-600 hover:underline">Quitar</button>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Lista Carrito --}}
                    <div class="space-y-3 max-h-64 overflow-y-auto">
                        @forelse($carrito as $item)
                            <div class="flex justify-between items-center text-sm border-b border-zinc-100 dark:border-zinc-800 pb-2">
                                <div class="flex-1 pr-2">
                                    <flux:text class="font-medium truncate">{{ $item['name'] }}</flux:text>
                                    <flux:text size="xs" class="text-zinc-500">{{ $item['cantidad'] }} x ${{ number_format($item['price'], 2) }}</flux:text>
                                </div>
                                <div class="flex items-center gap-1">
                                    <flux:button variant="ghost" icon="minus" size="xs" class="text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200" wire:click="quitarUnoDelCarrito({{ $item['id'] }})" tooltip="Quitar 1" />
                                    <flux:button variant="ghost" icon="pencil-square" size="xs" class="text-zinc-400 hover:text-indigo-600" wire:click="openCustomModal({{ $item['id'] }}, 'quitar')" tooltip="Quitar personalizado" />
                                    <flux:button variant="ghost" icon="trash" size="xs" class="text-red-500 hover:text-red-700" wire:click="quitarDelCarrito({{ $item['id'] }})" tooltip="Eliminar ítem" />
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-12 text-zinc-400 dark:text-zinc-500 italic">
                                <flux:icon.shopping-cart class="w-12 h-12 mb-3 opacity-20" />
                                <flux:text size="sm">Tu carrito está esperando productos...</flux:text>
                            </div>
                        @endforelse
                    </div>

                    {{-- Totales y Ajustes --}}
                    <div class="pt-4 border-t space-y-4">
                        <flux:input wire:model.live="global_adjustment" type="number" label="Ajuste (+/-)" size="sm" />
                        
                        <div class="bg-zinc-50 dark:bg-zinc-800/50 p-3 rounded-lg flex justify-between items-center">
                            <flux:text size="xs" class="uppercase font-bold">Total Final</flux:text>
                            <flux:heading size="xl" class="text-indigo-600">${{ number_format($this->totalFinal, 2) }}</flux:heading>
                        </div>

                        {{-- Medios de Pago --}}
                        <div class="space-y-4">
                            <flux:heading size="sm">Medios de Pago (RF-05 / RF-06)</flux:heading>
                            @if($this->montoRestante > 0.01)
                                <div class="flex gap-2 items-end">
                                    <div class="flex-1"><flux:select wire:model.live="medio_pago_id" label="Medio">
                                        <option value="">Elegir...</option>
                                        @foreach($mediosPago as $mp) <option value="{{ $mp->id }}">{{ $mp->nombre }}</option> @endforeach
                                    </flux:select></div>
                                    <div class="w-32"><flux:input wire:model.live="monto_pago_actual" type="number" label="Monto" /></div>
                                    <flux:button icon="bolt" variant="ghost" wire:click="autocompletarMonto" class="mb-0.5" />
                                    <flux:button icon="plus" variant="subtle" wire:click="agregarPago" class="mb-0.5" />
                                </div>
                            @endif

                            <div class="space-y-2">
                                @foreach($pagos_realizados as $index => $pago)
                                    <div class="flex justify-between items-center p-2 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200">
                                        <flux:text size="sm"><strong>{{ $pago['nombre'] }}:</strong> ${{ number_format($pago['monto'], 2) }}</flux:text>
                                        <flux:button icon="x-mark" size="xs" variant="ghost" wire:click="quitarPago({{ $index }})" />
                                    </div>
                                @endforeach
                            </div>

                            <div 
                                wire:key="balance-container-{{ count($pagos_realizados) }}" 
                                class="flex justify-between items-center p-3 rounded-xl border-2 border-dashed 
                                {{ $this->montoRestante <= 0.01 && collect($pagos_realizados)->sum('monto') <= $this->totalFinal ? 'bg-green-50 border-green-200' : 'bg-orange-50 border-orange-200' }}
                                {{ collect($pagos_realizados)->sum('monto') > $this->totalFinal ? 'bg-red-50 border-red-500' : '' }}"
                            >
                                @if(collect($pagos_realizados)->sum('monto') > $this->totalFinal)
                                    {{-- ALERTA ROJA: Si el usuario puso un descuento y ahora sobra plata --}}
                                    <flux:text size="xs" class="font-bold text-red-600 uppercase">⚠️ Monto excedido:</flux:text>
                                    <flux:heading size="lg" class="text-red-600">
                                        -${{ number_format(collect($pagos_realizados)->sum('monto') - $this->totalFinal, 2) }}
                                    </flux:heading>
                                @else
                                    {{-- ESTADO NORMAL: Falta pagar o está saldado --}}
                                    <flux:text size="xs" class="font-bold uppercase tracking-tighter">Restante:</flux:text>
                                    <flux:heading size="lg" class="{{ $this->montoRestante <= 0.01 ? 'text-green-600' : 'text-orange-600' }}">
                                        ${{ number_format($this->montoRestante, 2) }}
                                    </flux:heading>
                                @endif
                            </div>
                        </div>

                        {{-- ALERTA AZUL (Si paga una parte y hay cliente) --}}
                        @if($this->montoRestante > 0.01 && $cliente_id)
                            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 rounded-lg">
                                <flux:text size="xs" class="text-blue-700 dark:text-blue-400">
                                    ℹ️ Se cobrarán ${{ number_format(collect($pagos_realizados)->sum('monto'), 2) }} y el resto se cargará a la deuda de <strong>{{ $search_cliente }}</strong>.
                                </flux:text>
                            </div>
                        @endif

                        <flux:button 
                            variant="primary" 
                            class="w-full" 
                            wire:click="procesarVenta" 
                            icon="banknotes" 
                            :disabled="!$tipo_comprobante || empty($carrito) || ($this->montoRestante > 0.01 && !$cliente_id) || (collect($pagos_realizados)->sum('monto') > $this->totalFinal)"
                        >
                            {{ (collect($pagos_realizados)->sum('monto') > $this->totalFinal) ? 'Monto Excedido' : (($this->montoRestante > 0.01 && $cliente_id) ? 'Vender con Saldo Deudor' : 'Confirmar y Facturar') }}
                        </flux:button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- 3. CONTENIDO: PESTAÑA HISTORIAL --}}
    @if($tabActiva === 'historial')
    <div class="space-y-4">
        <div class="flex justify-between items-center">
            <flux:heading size="lg">Registro de Ventas</flux:heading>
            <div class="flex gap-2">
                <flux:input type="date" wire:model.live="fecha_desde" size="sm" aria-label="Desde fecha" />
                <flux:input type="date" wire:model.live="fecha_hasta" size="sm" aria-label="Hasta fecha" />
                <flux:select wire:model.live="filtroEstado" size="sm" class="w-48">
                    <option value="">Todos los estados</option>
                    <option value="PAGADO">Solo Pagados</option>
                    <option value="PENDIENTE">Solo Cta. Corriente</option>
                </flux:select>
            </div>
        </div>
        
        <div class="w-full overflow-hidden rounded-lg border border-zinc-200">
            <x-table>
                <x-slot:head>
                    <x-table.row>
                        <x-table.heading>Fecha</x-table.heading>
                        <x-table.heading>Cliente</x-table.heading>
                        <x-table.heading>Medio / Estado</x-table.heading>
                        <x-table.heading class="text-right">Monto Total</x-table.heading>
                        <x-table.heading class="text-right">Acciones</x-table.heading>
                    </x-table.row>
                </x-slot:head>

                <x-table.body>
                    @forelse($this->historialVentas as $venta)
                        <x-table.row :key="'venta-'.$venta->id">
                            {{-- Celda 1: Fecha --}}
                            <x-table.cell class="text-xs font-mono">
                                {{ $venta->fecha_emision->format('d/m/Y H:i') }}
                            </x-table.cell>

                            {{-- Celda 2: CLIENTE (RF-22) --}}
                            <x-table.cell>
                                @if($venta->cliente)
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ $venta->cliente->first_name }} {{ $venta->cliente->last_name }}
                                        </span>
                                        <span class="text-[10px] text-zinc-500 uppercase tracking-tighter">Vinculado</span>
                                    </div>
                                @else
                                    <span class="text-xs text-zinc-400 italic">Consumidor Final</span>
                                @endif
                            </x-table.cell>

                            {{-- Celda 3: Medio / Estado --}}
                            <x-table.cell>
                                @if($venta->estado === 'PENDIENTE')
                                    <flux:badge size="sm" color="red" variant="solid" icon="clock">Cuenta Corriente</flux:badge>
                                    @if($venta->pagos->isNotEmpty())
                                        <flux:text size="xs" class="block text-zinc-400 mt-0.5">Entrega parcial realizada</flux:text>
                                    @endif
                                @else
                                    @php
                                        $mediosUnicos = $venta->pagos->pluck('id_medio_pago')->unique()->count();
                                    @endphp
                                    <flux:badge size="sm" color="zinc" variant="outline">
                                        {{ $mediosUnicos > 1 ? 'Combinado' : ($venta->pagos->first()?->medioPago?->nombre ?? 'N/D') }}
                                    </flux:badge>
                                @endif
                            </x-table.cell>

                            {{-- Celda 4: Monto Total --}}
                            <x-table.cell class="text-right font-bold text-indigo-600">
                                ${{ number_format($venta->total, 2) }}
                            </x-table.cell>

                            {{-- Celda 5: Acciones --}}
                            <x-table.cell class="text-right">
                                <div class="flex gap-2 justify-end">
                                    {{-- Botón de Detalle --}}
                                    <flux:button 
                                        icon="information-circle" 
                                        size="xs" 
                                        variant="ghost" 
                                        wire:click="verDetalle({{ $venta->id }})" 
                                        tooltip="Ver detalle"
                                    />

                                    {{-- BOTÓN DE DESCARGA (RF-19) --}}
                                    <flux:button 
                                        icon="document-arrow-down" 
                                        size="xs" 
                                        variant="ghost" 
                                        class="text-indigo-600 hover:text-indigo-800"
                                        wire:click="descargarFactura({{ $venta->id }})" 
                                        tooltip="Descargar PDF"
                                    />
                                </div>
                            </x-table.cell>
                        </x-table.row>
                    @empty
                        {{-- Ajustamos el colspan a 5 porque ahora hay 5 columnas --}}
                        <x-table.row>
                            <x-table.cell colspan="5" class="text-center py-10 italic text-zinc-400">
                                No hay ventas registradas en este período.
                            </x-table.cell>
                        </x-table.row>
                    @endforelse
                </x-table.body>
            </x-table>
        </div>
        {{ $this->historialVentas->links() }}
    </div>
@endif

    {{-- 4. MODAL DETALLE DE VENTA --}}
    <flux:modal name="detalle-venta-modal" class="md:w-5/12">
        <div class="space-y-6">
            @if($facturaSeleccionada)
                <div>
                    <flux:heading size="lg">Detalle del Comprobante #{{ str_pad($facturaSeleccionada->id, 6, '0', STR_PAD_LEFT) }}</flux:heading>
                    <flux:subheading>{{ $facturaSeleccionada->tipo_comprobante }}</flux:subheading>
                </div>

                <div class="space-y-4">
                    <div class="border rounded-lg overflow-hidden border-zinc-200 dark:border-zinc-700">
                        <x-table>
                            <x-slot:head>
                                <x-table.row>
                                    <x-table.heading>Producto</x-table.heading>
                                    <x-table.heading class="text-center">Cant.</x-table.heading>
                                    <x-table.heading class="text-right">Subtotal</x-table.heading>
                                </x-table.row>
                            </x-slot:head>
                            <x-table.body>
                                @forelse($facturaSeleccionada->details as $detalle)
                                    <x-table.row>
                                        <x-table.cell class="font-medium">{{ $detalle->product->name }}</x-table.cell>
                                        <x-table.cell class="text-center">{{ $detalle->cantidad }}</x-table.cell>
                                        <x-table.cell class="text-right">${{ number_format($detalle->cantidad * $detalle->precio_unitario, 2) }}</x-table.cell>
                                    </x-table.row>
                                @empty
                                    <x-table.row>
                                        <x-table.cell colspan="3" class="text-center text-zinc-500 italic py-6">
                                            <flux:icon.inbox class="w-8 h-8 opacity-20 mx-auto mb-2" />
                                            No hay detalles registrados en el comprobante.
                                        </x-table.cell>
                                    </x-table.row>
                                @endforelse
                            </x-table.body>
                        </x-table>
                    </div>

                    {{-- Desglose MP --}}
                    @if($facturaSeleccionada->pagos->isNotEmpty())
                        <div class="space-y-2">
                            <flux:heading size="sm" class="text-zinc-500 uppercase tracking-wider">Pagos Realizados</flux:heading>
                            <div class="grid grid-cols-1 gap-2">
                                @foreach($facturaSeleccionada->pagos->groupBy('id_medio_pago') as $idMedio => $grupoPagos)
                                    <div class="flex justify-between items-center text-xs p-3 bg-zinc-50/50 border rounded-xl">
                                        <div class="flex items-center gap-2">
                                            <flux:icon.banknotes class="w-4 h-4 text-zinc-400" />
                                            <span class="font-medium">{{ $grupoPagos->first()->medioPago->nombre }}</span>
                                        </div>
                                        <span class="font-bold">${{ number_format($grupoPagos->sum('monto'), 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Resumen Consolidado --}}
                    <div class="space-y-3 p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border">
                        <div class="flex justify-between text-sm text-zinc-500 italic">
                            <span>Subtotal Medicamentos:</span>
                            <span>${{ number_format($facturaSeleccionada->total - $facturaSeleccionada->ajuste_global, 2) }}</span>
                        </div>
                        @if($facturaSeleccionada->ajuste_global != 0)
                            <div class="flex justify-between text-sm {{ $facturaSeleccionada->ajuste_global < 0 ? 'text-green-600' : 'text-orange-600' }}">
                                <span>Ajuste Global:</span>
                                <span class="font-medium">${{ number_format($facturaSeleccionada->ajuste_global, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between items-center py-2 border-t font-bold">
                            <flux:text class="uppercase">Total Comprobante:</flux:text>
                            <flux:heading size="lg" class="text-indigo-600">${{ number_format($facturaSeleccionada->total, 2) }}</flux:heading>
                        </div>
                        @php $pagado = $facturaSeleccionada->pagos->sum('monto'); $pendiente = $facturaSeleccionada->total - $pagado; @endphp
                        <div class="flex justify-between text-sm text-green-600 font-medium"><span>Monto Pagado:</span><span>${{ number_format($pagado, 2) }}</span></div>
                        @if($pendiente > 0.01)
                            <div class="flex justify-between items-center p-2 mt-2 bg-red-50 border-red-100 rounded-lg font-bold text-red-700 uppercase">
                                <span>Saldo Pendiente:</span><span>${{ number_format($pendiente, 2) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="flex flex-col items-center py-12"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mb-2"></div><flux:text>Cargando...</flux:text></div>
            @endif
            <div class="flex justify-end"><flux:modal.close><flux:button variant="ghost">Cerrar</flux:button></flux:modal.close></div>
        </div>
    </flux:modal>
    <flux:modal name="leaflet-modal" class="min-w-[35rem]">
        <div class="space-y-6">
            @if($viewingMedicine)
                <div>
                    <flux:heading size="lg">{{ $viewingMedicine->product->name }}</flux:heading>
                    <flux:subheading>
                        Dosis/Nivel: <strong>{{ $viewingMedicine->level ?: 'N/D' }}</strong>
                        @if($viewingMedicine->is_psychotropic)
                            <flux:badge variant="danger" size="sm" class="ml-2" inset="top bottom">Psicotrópico</flux:badge>
                        @endif
                    </flux:subheading>
                </div>

                <div class="bg-blue-50/50 dark:bg-zinc-800 p-4 rounded-lg border border-blue-100 dark:border-zinc-700">
                    <flux:heading size="sm" class="mb-2 uppercase text-blue-600 dark:text-blue-400 tracking-wider font-bold">Prospecto e Indicaciones</flux:heading>
                    @if($viewingMedicine->leaflet)
                        {{-- Usamos whitespace-pre-wrap para respetar los saltos de línea del texto --}}
                        <div class="text-sm text-zinc-700 dark:text-zinc-300 space-y-2 whitespace-pre-wrap">{{ $viewingMedicine->leaflet }}</div>
                    @else
                        <flux:text class="text-zinc-400 italic">No hay indicaciones clínicas cargadas para este producto.</flux:text>
                    @endif
                </div>
            @endif

            <div class="flex justify-end">
                <flux:modal.close>
                    <flux:button variant="primary">Entendido</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

<flux:modal name="exito-venta-modal" class="md:w-96 text-center">
    <div class="space-y-6">
        <div class="flex justify-center">
            <div class="bg-green-100 dark:bg-green-900/30 p-4 rounded-full">
                <flux:icon.check-circle class="text-green-600 w-12 h-12" />
            </div>
        </div>

        <div>
            <flux:heading size="lg">¡Venta Exitosa!</flux:heading>
            <flux:text>El comprobante se ha registrado correctamente en el sistema.</flux:text>
        </div>

        <div class="grid grid-cols-1 gap-2">
            {{-- Opción de IMPRIMIR/GUARDAR (PDF) --}}
            <flux:button 
                variant="primary" 
                icon="document-arrow-down" 
                wire:click="descargarFactura({{ $ultimaFacturaId }})"
            >
                Descargar / Imprimir Factura
            </flux:button>

            <flux:modal.close>
                <flux:button variant="ghost" class="w-full">Nueva Venta</flux:button>
            </flux:modal.close>
        </div>
    </div>
</flux:modal>

{{-- MODAL ENTRADA CANTIDAD PERSONALIZADA (Fase 11) --}}
<flux:modal name="custom-quantity-modal" class="md:w-96">
    <form wire:submit="processCustomQuantity" class="space-y-6">
        <div>
            <flux:heading size="lg">
                {{ $customOperation === 'agregar' ? 'Múltiples Unidades' : 'Quitar Múltiples' }}
            </flux:heading>
            <flux:subheading>Ingresa el monto a {{ $customOperation === 'agregar' ? 'añadir' : 'retirar' }} del Carrito</flux:subheading>
        </div>

        <flux:input wire:model="customQuantity" type="number" label="Cantidad" placeholder="1" min="1" required autofocus />

        <div class="flex justify-between gap-2">
            <flux:modal.close>
                <flux:button variant="ghost" wire:click="$reset(['customQuantity', 'customMedicineId', 'customOperation'])">Cancelar</flux:button>
            </flux:modal.close>
            <flux:button variant="primary" type="submit">Confirmar</flux:button>
        </div>
    </form>
</flux:modal>

<script>
    window.addEventListener('abrir-impresion', event => {
        // Abrimos la URL de la factura en una pestaña nueva/ventana popup
        window.open(event.detail.url, '_blank');
    });
</script>
</div>