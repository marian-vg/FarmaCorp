<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl max-w-none">
    {{-- Encabezado --}}
    <div class="flex justify-between items-center">
        <flux:heading level="1" size="lg">Gestión de Turnos de Caja</flux:heading>

        <flux:modal.trigger name="abrir-caja-form">
            <flux:button icon="plus" variant="primary">Nueva Apertura</flux:button>
        </flux:modal.trigger>
    </div>

    {{-- Tabs Manuales --}}
    <div class="flex gap-4 border-b border-zinc-200 dark:border-zinc-700 mb-6">
        <button 
            wire:click="$set('tabActiva', 'gestion')" 
            class="px-4 py-2 font-medium text-sm {{ $tabActiva === 'gestion' ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}"
        >
            Gestión Actual
        </button>
        <button 
            wire:click="$set('tabActiva', 'historial')" 
            class="px-4 py-2 font-medium text-sm {{ $tabActiva === 'historial' ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}"
        >
            Historial de Cierres
        </button>
    </div>

    @if($tabActiva === 'gestion')
        <div class="space-y-4">
            {{-- Filtros Rápidos Gestión --}}
            <div class="flex flex-wrap items-end gap-4 mb-2">
                <div class="flex-1">
                    <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar por usuario..." class="min-w-64">
                        <x-slot name="append">
                            <div x-data x-show="$wire.search !== ''" style="display: none;" class="flex items-center pe-2">
                                <flux:button variant="subtle" size="sm" icon="x-mark" wire:click="$set('search', '')" class="h-6 w-6 px-0" />
                            </div>
                        </x-slot>
                    </flux:input>
                </div>
                <flux:button wire:click="limpiarFiltros" variant="outline" icon="trash">Limpiar</flux:button>
            </div>

            {{-- Tabla Cajas Abiertas --}}
            <x-table>
                <x-table.head>
                    <x-table.heading>Usuario</x-table.heading>
                    <x-table.heading>Apertura</x-table.heading>
                    <x-table.heading>Monto Inicial</x-table.heading>
                    <x-table.heading>Estado</x-table.heading>
                    <x-table.heading class="text-right">Acciones</x-table.heading>
                </x-table.head>
                <x-table.body>
                        @forelse($this->cajas as $caja)
                            <x-table.row wire:key="gestion-{{ $caja->id }}">
                                <x-table.cell>
                                    <div class="flex items-center gap-2">
                                        <flux:avatar size="xs" :name="$caja->user->name" />
                                        <span>{{ $caja->user->name }}</span>
                                    </div>
                                </x-table.cell>
                                <x-table.cell>{{ $caja->fecha_apertura->format('d/m/Y H:i') }}</x-table.cell>
                                <x-table.cell>${{ number_format($caja->monto_inicial, 2) }}</x-table.cell>
                                <x-table.cell>
                                    <flux:badge color="green" inset="top bottom">Abierta</flux:badge>
                                </x-table.cell>
                                <x-table.cell class="text-right">
                                    <flux:button size="sm" variant="ghost" icon="eye" wire:click="verDetalle({{ $caja->id }})" />
                                </x-table.cell>
                            </x-table.row>
                        @empty
                            <x-table.row>
                                <x-table.cell colspan="5" class="py-10 text-center text-zinc-500">No hay cajas abiertas.</x-table.cell>
                            </x-table.row>
                        @endforelse
                </x-table.body>
            </x-table>
        </div>
    @endif

    @if($tabActiva === 'historial')
        <div class="space-y-6">
            {{-- NUEVO: Tarjetas de Resumen Analítico --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <flux:card class="flex items-center gap-4 border-l-4 border-green-500">
                    <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                        <flux:icon.arrow-trending-up class="text-green-600" />
                    </div>
                    <div>
                        <flux:text size="xs" class="text-zinc-500 uppercase font-bold">Mejor Día</flux:text>
                        <flux:heading size="lg">${{ number_format($this->analiticaResumen['mejor_monto'], 2) }}</flux:heading>
                        <flux:text size="xs" class="text-zinc-400">{{ $this->analiticaResumen['mejor_dia'] }}</flux:text>
                    </div>
                </flux:card>

                <flux:card class="flex items-center gap-4 border-l-4 border-indigo-500">
                    <div class="p-3 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                        <flux:icon.chart-bar class="text-indigo-600" />
                    </div>
                    <div>
                        <flux:text size="xs" class="text-zinc-500 uppercase font-bold">Promedio Diario</flux:text>
                        <flux:heading size="lg">${{ number_format($this->analiticaResumen['promedio'], 2) }}</flux:heading>
                        <flux:text size="xs" class="text-zinc-400">Últimos 7 días</flux:text>
                    </div>
                </flux:card>

                <flux:card class="flex items-center gap-4 border-l-4 border-zinc-500">
                    <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                        <flux:icon.banknotes class="text-zinc-600" />
                    </div>
                    <div>
                        <flux:text size="xs" class="text-zinc-500 uppercase font-bold">Total Acumulado</flux:text>
                        <flux:heading size="lg">${{ number_format(array_sum($this->estadisticasSieteDias['datos']), 2) }}</flux:heading>
                        <flux:text size="xs" class="text-zinc-400">Semana completa</flux:text>
                    </div>
                </flux:card>
            </div>

            {{-- Gráfico con Alpine.js (Solución al cuadro negro) --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-6">
                <flux:heading size="md" class="mb-4">Tendencia de Recaudación Semanal</flux:heading>
                <div 
                    x-data="{
                        init() {
                            let chartData = @js($this->estadisticasSieteDias);
                            new Chart(this.$refs.canvas, {
                                type: 'bar',
                                data: {
                                    labels: chartData.labels,
                                    datasets: [{
                                        data: chartData.datos,
                                        backgroundColor: '#4f46e5',
                                        borderRadius: 4
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: { legend: { display: false } },
                                    scales: {
                                        y: { beginAtZero: true, ticks: { callback: (v) => '$' + v } }
                                    }
                                }
                            });
                        }
                    }"
                    class="h-64 w-full"
                >
                    <canvas x-ref="canvas"></canvas>
                </div>
            </div>

            {{-- Filtros Historial --}}
            <div class="flex flex-wrap items-end gap-4">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar usuario..." class="flex-1">
                    <x-slot name="append">
                        <div x-data x-show="$wire.search !== ''" style="display: none;" class="flex items-center pe-2">
                            <flux:button variant="subtle" size="sm" icon="x-mark" wire:click="$set('search', '')" class="h-6 w-6 px-0" />
                        </div>
                    </x-slot>
                </flux:input>
                <flux:input wire:model.live="fecha_desde" type="date" label="Desde" />
                <flux:input wire:model.live="fecha_hasta" type="date" label="Hasta" />
                <flux:button wire:click="limpiarFiltros" variant="outline">Limpiar</flux:button>
            </div>

            {{-- Tabla Historial --}}
            <x-table>
                <x-table.head>
                    <x-table.heading>Usuario</x-table.heading>
                    <x-table.heading>Apertura</x-table.heading>
                    <x-table.heading>Cierre</x-table.heading>
                    <x-table.heading class="text-right">Monto Final</x-table.heading>
                    <x-table.heading class="text-right">Acciones</x-table.heading>
                </x-table.head>
                <x-table.body>
                        @forelse($this->historialCajas as $caja)
                            <x-table.row wire:key="historial-{{ $caja->id }}">
                                <x-table.cell>{{ $caja->user->name }}</x-table.cell>
                                <x-table.cell>{{ $caja->fecha_apertura->format('d/m H:i') }}</x-table.cell>
                                <x-table.cell>{{ $caja->fecha_cierre->format('d/m H:i') }}</x-table.cell>
                                <x-table.cell class="text-right font-bold text-indigo-600">${{ number_format($caja->monto_final, 2) }}</x-table.cell>
                                <x-table.cell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <flux:button size="sm" variant="ghost" icon="eye" wire:click="verDetalle({{ $caja->id }})" />
                                        <flux:button size="sm" variant="ghost" icon="document-arrow-down" wire:click="descargarReporte({{ $caja->id }})" />
                                    </div>
                                </x-table.cell>
                            </x-table.row>
                        @empty
                            <x-table.row>
                                <x-table.cell colspan="5" class="py-10 text-center text-zinc-500">No hay registros históricos.</x-table.cell>
                            </x-table.row>
                        @endforelse
                </x-table.body>
            </x-table>
            {{ $this->historialCajas->links() }}
        </div>
    @endif

    {{-- MODAL APERTURA --}}
    <flux:modal name="abrir-caja-form" class="min-w-120">
        <form wire:submit="abrirCaja" class="space-y-6">
            <div>
                <flux:heading size="lg">Abrir Nueva Caja</flux:heading>
                <flux:subheading>Asigna un cajero y el monto inicial.</flux:subheading>
            </div>
            <flux:select wire:model="user_id" label="Usuario" placeholder="Selecciona..." required>
                @foreach($this->usuarios as $user)
                    <flux:select.option value="{{ $user->id }}">{{ $user->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:input wire:model="monto_inicial" type="number" step="0.01" label="Monto Inicial ($)" icon="currency-dollar" required />
            @error('caja_status') <flux:error>{{ $message }}</flux:error> @enderror
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">Confirmar</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- MODAL DETALLE LATERAL --}}
<flux:modal name="detalle-caja-panel" variant="side" class="min-w-180">
    @if($cajaSeleccionada)
        <div class="space-y-6">
            <flux:heading size="xl">Detalle de Caja #{{ $cajaSeleccionada->id }}</flux:heading>
            
            <div class="grid grid-cols-3 gap-4">
                <flux:card>
                    <flux:text size="xs">Inicial</flux:text>
                    <flux:heading size="md">${{ number_format($cajaSeleccionada->monto_inicial, 2) }}</flux:heading>
                </flux:card>
                <flux:card class="bg-indigo-50 dark:bg-indigo-900/20">
                    <flux:text size="xs">Saldo Actual</flux:text>
                    <flux:heading size="md" class="text-indigo-600 dark:text-indigo-400">${{ number_format($this->saldoActual, 2) }}</flux:heading>
                </flux:card>
                <flux:card>
                    <flux:text size="xs">Estado</flux:text>
                    <flux:badge color="{{ $cajaSeleccionada->fecha_cierre ? 'zinc' : 'green' }}">
                        {{ $cajaSeleccionada->fecha_cierre ? 'Cerrada' : 'Abierta' }}
                    </flux:badge>
                </flux:card>
            </div>

            {{-- Desglose MP Mejorado --}}
            <div class="mt-4">
                <flux:heading size="sm" class="mb-3 uppercase tracking-wider text-zinc-500">Desglose por Medios de Pago</flux:heading>
                
                @if($this->totalesPorMedio->isNotEmpty())
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($this->totalesPorMedio as $nombre => $total)
                            <div class="p-3 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm flex justify-between items-center bg-zinc-50/50 dark:bg-zinc-800/50">
                                <span class="font-medium">{{ $nombre }}</span>
                                <span class="font-bold {{ $total >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $total >= 0 ? '+' : '-' }}${{ number_format(abs($total), 2) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-4 border border-dashed border-zinc-300 dark:border-zinc-700 rounded-lg text-center">
                        <flux:text size="sm" class="text-zinc-500 italic">No se registran movimientos externos al efectivo inicial aún.</flux:text>
                    </div>
                @endif
            </div>

            <flux:separator />

            {{-- Tabla Movimientos con Botones de Registro --}}
            {{-- Tabla Movimientos con Botones de Registro y Medio de Pago --}}
            <div>
                <div class="flex justify-between items-center mb-3">
                    <flux:heading size="sm" class="uppercase tracking-wider text-zinc-500">Historial de Operaciones</flux:heading>
                    
                    @if(!$cajaSeleccionada->fecha_cierre)
                        <div class="flex gap-2">
                            <flux:button 
                                size="sm" 
                                variant="ghost" 
                                icon="plus-circle" 
                                class="text-green-600 hover:bg-green-50" 
                                wire:click="prepararMovimiento('INGRESO')"
                            >
                                Ingreso
                            </flux:button>
                            <flux:button 
                                size="sm" 
                                variant="ghost" 
                                icon="minus-circle" 
                                class="text-red-600 hover:bg-red-50" 
                                wire:click="prepararMovimiento('EGRESO')"
                            >
                                Egreso
                            </flux:button>
                        </div>
                    @endif
                </div>

                <x-table>
                    <x-table.head>
                        <x-table.heading>Hora</x-table.heading>
                        <x-table.heading>Responsable</x-table.heading>
                        <x-table.heading>Medio de Pago</x-table.heading>
                        <x-table.heading class="text-right">Monto</x-table.heading>
                    </x-table.head>
                    <x-table.body>
                            @forelse($cajaSeleccionada->movimientos()->with('user', 'medioPago')->get() as $mov)
                                <x-table.row>
                                    <x-table.cell class="text-xs font-mono">{{ $mov->fecha_movimiento->format('H:i') }}</x-table.cell>
                                    <x-table.cell class="text-xs">{{ $mov->user->name }}</x-table.cell>
                                    <x-table.cell class="text-xs">
                                        <flux:badge size="sm" variant="outline" color="zinc">
                                            {{ $mov->medioPago->nombre }}
                                        </flux:badge>
                                    </x-table.cell>
                                    <x-table.cell class="text-right font-bold {{ $mov->tipo_movimiento === 'INGRESO' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $mov->tipo_movimiento === 'INGRESO' ? '+' : '-' }}${{ number_format($mov->monto, 2) }}
                                    </x-table.cell>
                                </x-table.row>
                            @empty
                                <x-table.row>
                                    <x-table.cell colspan="4" class="py-8 text-center text-zinc-500 italic">
                                        Sin ingresos ni egresos registrados en este turno de caja.
                                    </x-table.cell>
                                </x-table.row>
                            @endforelse
                    </x-table.body>
                </x-table>
            </div>

            <div class="flex justify-end gap-2 pt-4">
                @if(!$cajaSeleccionada->fecha_cierre)
                    <flux:modal.trigger name="confirm-admin-close-caja">
                        <flux:button variant="danger" icon="lock-closed">Cerrar Turno</flux:button>
                    </flux:modal.trigger>
                @endif
                <flux:modal.close><flux:button variant="ghost">Cerrar Vista</flux:button></flux:modal.close>
            </div>
        </div>
    @endif
</flux:modal>

    {{-- MODAL CIERRE FINAL --}}
    <flux:modal name="confirm-admin-close-caja" class="min-w-lg">
        <form wire:submit="cerrarCaja" class="space-y-6">
            <flux:heading size="lg" class="text-red-600">Auditoría Final</flux:heading>
            @if($cajaSeleccionada)
                <div class="p-4 bg-zinc-50 dark:bg-zinc-800 rounded text-center">
                    <flux:text>Monto Esperado:</flux:text>
                    <flux:heading size="xl" class="text-indigo-600">${{ number_format($this->saldoActual, 2) }}</flux:heading>
                </div>
            @endif
            <flux:textarea wire:model="observaciones_cierre" label="Observaciones" placeholder="Justifica faltantes..." />
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button type="submit" variant="danger">Confirmar Cierre</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- MODAL REGISTRO MOVIMIENTO --}}
    <flux:modal name="registro-movimiento-form" class="min-w-120">
        <form wire:submit="registrarMovimiento" class="space-y-6">
            <flux:heading size="lg">Registrar Movimiento</flux:heading>
            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="movimiento_monto" type="number" step="0.01" label="Monto" required />
                <flux:select wire:model="movimiento_medio_pago" label="Medio" required>
                    @foreach($this->mediosPago as $mp)
                        <flux:select.option value="{{ $mp->id }}">{{ $mp->nombre }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <flux:input wire:model="movimiento_motivo" label="Motivo" required />
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">Guardar</flux:button>
            </div>
        </form>
    </flux:modal>
</div>