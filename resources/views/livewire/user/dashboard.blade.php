<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="flex justify-between items-center bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 p-6 rounded-lg">
        <div>
            <flux:heading level="1" size="xl">Panel Operativo de Terminal</flux:heading>
            <flux:subheading>Bienvenido/a, {{ $user->name }}</flux:subheading>
        </div>
        
        <div>
            @if($this->cajaAbierta)
                <flux:badge color="green" inset="top bottom">Turno Activo</flux:badge>
            @else
                <flux:badge color="red" inset="top bottom">Turno Cerrado</flux:badge>
            @endif
        </div>
    </div>

    {{-- Tabs Manuales usando Livewire (Flux UI Free no provee Tabs) --}}
    <div class="flex gap-4 border-b border-zinc-200 dark:border-zinc-700 mb-6 mt-4">
        <button 
            wire:click="$set('tabActiva', 'gestion')" 
            class="px-4 py-2 font-medium text-sm {{ $tabActiva === 'gestion' ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}"
        >
            Turno Actual
        </button>
        <button 
            wire:click="$set('tabActiva', 'historial')" 
            class="px-4 py-2 font-medium text-sm {{ $tabActiva === 'historial' ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}"
        >
            Mis Turnos Anteriores
        </button>
    </div>

    @if($tabActiva === 'gestion')
    <div class="space-y-4">

    @if(!$this->cajaAbierta)
        <div class="flex flex-col items-center justify-center p-12 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-dashed border-zinc-300 dark:border-zinc-600 mt-4">
            <flux:icon.archive-box class="w-16 h-16 text-zinc-400 mb-4" />
            <flux:heading size="xl" class="mb-2">Ningún turno activo</flux:heading>
            <flux:text class="text-center text-zinc-500 max-w-sm mb-6">
                Para comenzar a realizar operaciones y transacciones, debes iniciar tu caja declarando el monto inicial en el cajón físico.
            </flux:text>
            <flux:modal.trigger name="abrir-caja-form">
                <flux:button variant="primary" icon="play">Abrir Mi Caja</flux:button>
            </flux:modal.trigger>
        </div>

        {{-- MODAL Apertura --}}
        <flux:modal name="abrir-caja-form" class="min-w-md">
            <form wire:submit="abrirCaja" class="space-y-6">
                <div>
                    <flux:heading size="lg">Iniciar Nuevo Turno</flux:heading>
                    <flux:subheading>Por favor, declara cuánto dinero en efectivo hay inicialmente en tu caja registradora.</flux:subheading>
                </div>

                <div class="space-y-2">
                    <flux:input wire:model="monto_inicial" type="number" step="0.01" min="0" label="Monto Inicial en Cajón" placeholder="Ej: 1500.00" icon="currency-dollar" required />
                    @if($errors->has('caja_status'))
                        <flux:error class="mt-2 text-sm text-red-600">{{ $errors->first('caja_status') }}</flux:error>
                    @endif
                </div>

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancelar</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">Comenzar Turno</flux:button>
                </div>
            </form>
        </flux:modal>

    @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
            <flux:card>
                <flux:text size="sm" class="mb-2 block text-zinc-500">Monto Inicial Declarado</flux:text>
                <flux:heading size="lg">${{ number_format($this->cajaAbierta->monto_inicial, 2) }}</flux:heading>
                <flux:text size="xs" class="mt-1 text-zinc-400">Apertura: {{ $this->cajaAbierta->fecha_apertura->format('H:i') }}</flux:text>
            </flux:card>

            <flux:card class="bg-zinc-50 dark:bg-zinc-800/50 border-zinc-300 dark:border-zinc-600">
                <flux:text size="sm" class="mb-2 block text-zinc-600 dark:text-zinc-400">Total Ingresos</flux:text>
                <flux:heading size="lg" class="text-green-600 dark:text-green-400">
                    +${{ number_format($this->cajaAbierta->movimientos->where('tipo_movimiento', 'INGRESO')->sum('monto'), 2) }}
                </flux:heading>
            </flux:card>

            <flux:card class="bg-indigo-50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-800/50 relative overflow-hidden ring-1 ring-indigo-500">
                <flux:text size="sm" class="mb-2 block text-indigo-700 dark:text-indigo-300 font-medium">Saldo Actual</flux:text>
                <flux:heading size="2xl" class="text-indigo-900 dark:text-indigo-100">${{ number_format($this->saldoActual, 2) }}</flux:heading>
                
                <div class="absolute right-0 bottom-0 opacity-10 blur-sm pointer-events-none transform translate-x-4 translate-y-4">
                    <flux:icon.currency-dollar class="w-32 h-32" />
                </div>
            </flux:card>
        </div>

        <div class="flex items-center justify-between gap-4 mt-4">
            <flux:heading size="lg">Movimientos del Turno</flux:heading>
            
            <div class="flex items-center gap-2">
                <flux:modal.trigger name="registro-movimiento-form">
                    <flux:button wire:click="$set('movimiento_tipo', 'EGRESO')" icon="arrow-down-right" variant="danger" ghost>Registrar Retiro</flux:button>
                </flux:modal.trigger>
                
                <flux:modal.trigger name="registro-movimiento-form">
                    <flux:button wire:click="$set('movimiento_tipo', 'INGRESO')" icon="arrow-up-right" variant="primary" ghost>Registrar Ingreso</flux:button>
                </flux:modal.trigger>
                
                <div class="w-px h-6 bg-zinc-300 dark:bg-zinc-700 mx-2"></div>
                
                <flux:modal.trigger name="confirm-close-caja">
                    <flux:button variant="danger" icon="lock-closed">Cerrar Mi Turno</flux:button>
                </flux:modal.trigger>
            </div>
        </div>

        <div class="w-full overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700 mt-2">
            <x-table>
                <x-table.head>
                    <x-table.heading>Hora</x-table.heading>
                    <x-table.heading>Tipo</x-table.heading>
                    <x-table.heading>Responsable</x-table.heading>
                    <x-table.heading>Medio de Pago</x-table.heading>
                    <x-table.heading>Motivo</x-table.heading>
                    <x-table.heading class="text-right">Monto</x-table.heading>
                </x-table.head>

                <x-table.body>
                    @forelse($this->cajaAbierta->movimientos()->with(['user', 'medioPago'])->orderBy('fecha_movimiento', 'desc')->get() as $movimiento)
                        <x-table.row>
                            <x-table.cell class="text-xs font-mono">
                                {{ \Carbon\Carbon::parse($movimiento->fecha_movimiento)->format('H:i:s') }}
                            </x-table.cell>
                            
                            <x-table.cell>
                                @if($movimiento->tipo_movimiento === 'INGRESO')
                                    <flux:badge color="green" size="sm" inset="top bottom">Ingreso</flux:badge>
                                @else
                                    <flux:badge color="red" size="sm" inset="top bottom">Egreso</flux:badge>
                                @endif
                            </x-table.cell>

                            <x-table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:avatar size="xs" initials="{{ collect(explode(' ', $movimiento->user->name))->map(fn($n) => $n[0])->join('') }}" />
                                    <span class="text-xs">{{ $movimiento->user->name }}</span>
                                </div>
                            </x-table.cell>

                            <x-table.cell class="text-xs">
                                {{ $movimiento->medioPago->nombre }}
                            </x-table.cell>
                            
                            <x-table.cell class="text-xs">
                                {{ $movimiento->motivo }}
                            </x-table.cell>
                            
                            <x-table.cell class="text-right font-bold text-sm">
                                <span class="{{ $movimiento->tipo_movimiento === 'INGRESO' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $movimiento->tipo_movimiento === 'INGRESO' ? '+' : '-' }}${{ number_format((float)$movimiento->monto, 2) }}
                                </span>
                            </x-table.cell>
                        </x-table.row>
                    @empty
                        <x-table.row>
                            <x-table.cell colspan="6" class="py-8 text-center text-zinc-500">
                                Sin movimientos registrados en este turno.
                            </x-table.cell>
                        </x-table.row>
                    @endforelse
                </x-table.body>
            </x-table>
        </div>

        {{-- MODAL Registro de Movimientos --}}
<flux:modal name="registro-movimiento-form" class="min-w-lg">
    <form wire:submit="registrarMovimiento" class="space-y-6">
        <div>
            {{-- Título Dinámico: Cambia según el botón presionado --}}
            <flux:heading size="lg">
                {{ $movimiento_tipo === 'INGRESO' ? 'Registrar Ingreso de Dinero' : 'Registrar Egreso / Retiro' }}
            </flux:heading>
            <flux:subheading>
                {{ $movimiento_tipo === 'INGRESO' ? 'Indica el monto que está entrando a la caja.' : 'Indica el monto que sale de la caja.' }}
            </flux:subheading>
        </div>

        <div class="space-y-4">
            <flux:input 
                wire:model="movimiento_monto" 
                type="number" 
                step="0.01" 
                min="0.01" 
                label="Monto a {{ $movimiento_tipo === 'INGRESO' ? 'Ingresar' : 'Retirar' }}" 
                icon="currency-dollar" 
                placeholder="0.00" 
                required 
            />
            
            <flux:select wire:model="movimiento_medio_pago" label="Medio de Pago" placeholder="Selecciona..." required>
                @foreach($this->mediosPago as $mp)
                    <flux:select.option value="{{ $mp->id }}">{{ $mp->nombre }}</flux:select.option>
                @endforeach
            </flux:select>
            
            <flux:textarea 
                wire:model="movimiento_motivo" 
                label="Motivo o Detalle" 
                placeholder="{{ $movimiento_tipo === 'INGRESO' ? 'Ej: Aporte de cambio, cobro manual...' : 'Ej: Pago a proveedor, retiro de efectivo...' }}" 
                required 
            />
            
            {{-- ELIMINAMOS EL RADIO GROUP PORQUE YA SABEMOS EL TIPO --}}

            @if($errors->has('movimiento_status'))
                <flux:error class="text-sm text-red-600">{{ $errors->first('movimiento_status') }}</flux:error>
            @endif
        </div>

        <div class="flex justify-end gap-2">
            <flux:modal.close>
                <flux:button variant="ghost">Cancelar</flux:button>
            </flux:modal.close>
            <flux:button type="submit" variant="{{ $movimiento_tipo === 'INGRESO' ? 'primary' : 'danger' }}">
                Confirmar {{ $movimiento_tipo === 'INGRESO' ? 'Ingreso' : 'Retiro' }}
            </flux:button>
        </div>
    </form>
</flux:modal>

        {{-- MODAL Confirmar Cierre (Mejorado) --}}
<flux:modal name="confirm-close-caja" class="min-w-100">
    <form wire:submit="cerrarMiTurno" class="space-y-6">
        <flux:heading size="lg" class="text-red-600">Resumen de Arqueo para Cierre</flux:heading>

        <div class="space-y-3">
            <flux:text size="sm">Verifica los totales antes de cerrar el turno:</flux:text>
            
            <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 divide-y divide-zinc-200 dark:divide-zinc-700">
                {{-- Fila de Monto Inicial --}}
                <div class="flex justify-between p-3">
                    <flux:text size="sm">Monto Inicial (Efectivo)</flux:text>
                    <flux:text size="sm" class="font-medium">${{ number_format($this->cajaAbierta->monto_inicial, 2) }}</flux:text>
                </div>

                {{-- Desglose por Medios de Pago --}}
                @foreach($this->totalesPorMedio as $nombre => $total)
                    <div class="flex justify-between p-3">
                        <flux:text size="sm">{{ $nombre }}</flux:text>
                        <flux:text size="sm" class="font-medium {{ $total >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            ${{ number_format($total, 2) }}
                        </flux:text>
                    </div>
                @endforeach

                {{-- Gran Total Final --}}
                <div class="flex justify-between p-3 bg-indigo-50 dark:bg-indigo-900/20">
                    <flux:text size="sm" class="font-bold text-indigo-900 dark:text-indigo-100">TOTAL A ENTREGAR</flux:text>
                    <flux:text size="sm" class="font-bold text-indigo-900 dark:text-indigo-100">${{ number_format($this->saldoActual, 2) }}</flux:text>
                </div>
            </div>
        </div>

        <flux:textarea 
            wire:model="observaciones_cierre" 
            label="Detalles o Justificaciones (Opcional)" 
            placeholder="Ej: Faltante de $10 debido a cambio suelto, sobrante de $5..."
            rows="2"
        ></flux:textarea>

        <flux:text size="xs" class="text-zinc-500">
            * El total en Efectivo debe ser: Monto Inicial + Movimientos en Efectivo.
        </flux:text>

        <div class="flex justify-end gap-2">
            <flux:modal.close>
                <flux:button variant="ghost">Revisar de nuevo</flux:button>
            </flux:modal.close>
            <flux:button type="submit" variant="danger">Confirmar y Cerrar</flux:button>
        </div>
    </form>
</flux:modal>

    @endif
    </div>
    @endif

    {{-- PESTAÑA HISTORIAL DEL EMPLEADO --}}
    @if($tabActiva === 'historial')
    <div>
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <flux:heading size="lg">Historial de Turnos</flux:heading>
                <flux:subheading>Listado de todas tus operaciones de caja pasadas y cerradas.</flux:subheading>
            </div>
        </div>

        <div class="w-full overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
            <x-table>
                <x-table.head>
                    <x-table.heading>Apertura</x-table.heading>
                    <x-table.heading>Cierre</x-table.heading>
                    <x-table.heading>Monto Declarado</x-table.heading>
                    <x-table.heading>Recaudación Final</x-table.heading>
                    <x-table.heading class="text-right">Acciones</x-table.heading>
                </x-table.head>

                <x-table.body>
                    @forelse($this->historialCajas as $caja)
                        <x-table.row>
                            <x-table.cell>{{ $caja->fecha_apertura->format('d/m/Y H:i') }}</x-table.cell>
                            <x-table.cell>{{ $caja->fecha_cierre ? $caja->fecha_cierre->format('d/m/Y H:i') : 'Activa' }}</x-table.cell>
                            <x-table.cell>${{ number_format($caja->monto_inicial, 2) }}</x-table.cell>
                            <x-table.cell class="font-bold text-indigo-600 dark:text-indigo-400">
                                ${{ number_format($caja->monto_final, 2) }}
                            </x-table.cell>
                            <x-table.cell class="text-right">
                                <flux:button size="sm" variant="ghost" icon="document-arrow-down" wire:click="descargarReporte({{ $caja->id }})" class="text-indigo-600 hover:text-indigo-700" />
                            </x-table.cell>
                        </x-table.row>
                    @empty
                        <x-table.row>
                            <x-table.cell colspan="5" class="text-center py-10 text-zinc-500">
                                Aún no tienes cierres de caja registrados en el sistema.
                            </x-table.cell>
                        </x-table.row>
                    @endforelse
                </x-table.body>
            </x-table>
        </div>

        <div class="mt-4">
            {{ $this->historialCajas->links() }}
        </div>
    </div>
    @endif
</div>
