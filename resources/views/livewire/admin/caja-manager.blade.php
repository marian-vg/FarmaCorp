<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl max-w-none">
    {{-- Encabezado --}}
    <div class="flex justify-between items-center">
        <flux:heading level="1" size="lg">Gestión de Turnos de Caja</flux:heading>

        <flux:modal.trigger name="abrir-caja-form">
            <flux:button icon="plus" variant="primary">Nueva Apertura</flux:button>
        </flux:modal.trigger>
    </div>

    {{-- Filtros y Búsqueda (RF-04) --}}
    <div class="flex flex-wrap items-end gap-4 mb-2">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar por usuario..." class="min-w-64" />
        </div>
        
        <flux:select wire:model.live="filtro_usuario" placeholder="Filtrar por Empleado" class="w-48">
            <flux:select.option value="">Todos los empleados</flux:select.option>
            @foreach($this->usuarios as $u)
                <flux:select.option value="{{ $u->id }}">{{ $u->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:input wire:model.live="fecha_desde" type="date" label="Desde" class="w-40" />
        <flux:input wire:model.live="fecha_hasta" type="date" label="Hasta" class="w-40" />

        <flux:button wire:click="limpiarFiltros" variant="outline" icon="trash">Limpiar</flux:button>
    </div>

    {{-- Tabla Principal --}}
    <div class="w-full overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Usuario (Credencial)</flux:table.column>
                <flux:table.column>Apertura</flux:table.column>
                <flux:table.column>Monto Inicial</flux:table.column>
                <flux:table.column>Cierre</flux:table.column>
                <flux:table.column>Monto Final</flux:table.column>
                <flux:table.column>Estado</flux:table.column>
                <flux:table.column align="end">Acciones</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($this->cajas as $caja)
                    <flux:table.row :key="$caja->id">
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:avatar size="xs" :name="$caja->user->name" />
                                <span>{{ $caja->user->name }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>{{ $caja->fecha_apertura->format('d/m/Y H:i') }}</flux:table.cell>
                        <flux:table.cell>${{ number_format($caja->monto_inicial, 2) }}</flux:table.cell>
                        <flux:table.cell>
                            @if($caja->fecha_cierre)
                                {{ $caja->fecha_cierre->format('d/m H:i') }}
                            @else
                                <flux:badge color="zinc" size="sm">Pendiente</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($caja->fecha_cierre)
                                ${{ number_format($caja->monto_final, 2) }}
                            @else
                                -
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($caja->fecha_cierre)
                                <flux:badge color="zinc" inset="top bottom">Cerrada</flux:badge>
                            @else
                                <flux:badge color="green" inset="top bottom">Abierta (En curso)</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:button 
                                size="sm" 
                                variant="ghost" 
                                icon="eye" 
                                wire:click="verDetalle({{ $caja->id }})" 
                            />
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center py-10 text-zinc-500">
                            No hay registros de caja creados todavía.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    {{-- Paginación --}}
    <div class="mt-4">
        {{ $this->cajas->links() }}
    </div>

    {{-- MODAL 1: Formulario de Apertura (RF-01) --}}
    <flux:modal name="abrir-caja-form" class="min-w-[30rem]">
        <form wire:submit="abrirCaja" class="space-y-6">
            <div>
                <flux:heading size="lg">Abrir Nueva Caja</flux:heading>
                <flux:subheading>Asigna un usuario y el monto inicial para el turno.</flux:subheading>
            </div>

            <flux:select wire:model="user_id" label="Asignar a Usuario" placeholder="Selecciona un cajero..." required>
                @foreach($this->usuarios as $user)
                    <flux:select.option value="{{ $user->id }}">{{ $user->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input 
                wire:model="monto_inicial" 
                type="number" 
                step="0.01" 
                label="Monto Inicial ($)" 
                icon="currency-dollar" 
                required 
            />

            @error('caja_status')
                <div class="mt-2">
                    <flux:error>{{ $message }}</flux:error>
                </div>
            @enderror

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Confirmar Apertura</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- MODAL 2: Panel Lateral de Detalles (RF-11/12) --}}
    {{-- Lo pusimos al final para que no se repita en el bucle --}}
    <flux:modal name="detalle-caja-panel" variant="side" class="min-w-[35rem]">
        @if($cajaSeleccionada)
            <div class="space-y-6">
                <div>
                    <flux:heading size="xl">Detalle de Caja #{{ $cajaSeleccionada->id }}</flux:heading>
                    <flux:subheading>Responsable: {{ $cajaSeleccionada->user->name }}</flux:subheading>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <flux:card>
                        <flux:text size="sm" class="mb-2 block text-zinc-500">Monto Inicial</flux:text>
                        <flux:heading size="lg">${{ number_format($cajaSeleccionada->monto_inicial, 2) }}</flux:heading>
                    </flux:card>
                    
                    <flux:card class="bg-zinc-50 dark:bg-zinc-800/50 border-zinc-300 dark:border-zinc-600">
                        <flux:text size="sm" class="mb-2 block text-zinc-600 dark:text-zinc-400">Saldo Actual</flux:text>
                        <flux:heading size="xl" class="text-indigo-600 dark:text-indigo-400">${{ number_format($this->saldoActual, 2) }}</flux:heading>
                    </flux:card>
                    
                    <flux:card>
                        <flux:text size="sm" class="mb-2 block text-zinc-500">Estado Actual</flux:text>
                        @if($cajaSeleccionada->fecha_cierre)
                            <flux:badge color="zinc">Cerrada el {{ $cajaSeleccionada->fecha_cierre->format('d/m H:i') }}</flux:badge>
                        @else
                            <flux:badge color="green">Abierta desde {{ $cajaSeleccionada->fecha_apertura->format('d/m H:i') }}</flux:badge>
                        @endif
                    </flux:card>
                </div>

                <flux:separator />

                <div class="flex justify-between items-center mt-6 mb-4">
                    <flux:heading size="md">Movimientos de la Caja</flux:heading>
                    
                    @if(!$cajaSeleccionada->fecha_cierre)
                    <div class="flex gap-2">
                        <flux:button size="sm" icon="arrow-down-circle" variant="ghost" class="text-green-600 hover:text-green-700" wire:click="prepararMovimiento('INGRESO')">Registrar Ingreso</flux:button>
                        <flux:button size="sm" icon="arrow-up-circle" variant="ghost" class="text-red-600 hover:text-red-700" wire:click="prepararMovimiento('EGRESO')">Registrar Egreso</flux:button>
                    </div>
                    @endif
                </div>

                <div class="w-full overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Tipo</flux:table.column>
                            <flux:table.column>Motivo</flux:table.column>
                            <flux:table.column>Medio de Pago</flux:table.column>
                            <flux:table.column align="end">Monto</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @forelse($cajaSeleccionada->movimientos as $movimiento)
                                <flux:table.row :key="$movimiento->id">
                                    <flux:table.cell>
                                        @if($movimiento->tipo_movimiento === 'INGRESO')
                                            <flux:badge color="green" size="sm" inset="top bottom">Ingreso</flux:badge>
                                        @else
                                            <flux:badge color="red" size="sm" inset="top bottom">Egreso</flux:badge>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell>{{ $movimiento->motivo }}</flux:table.cell>
                                    <flux:table.cell>
                                        {{ $movimiento->medioPago ? $movimiento->medioPago->nombre : 'N/A' }}
                                    </flux:table.cell>
                                    <flux:table.cell align="end">${{ number_format($movimiento->monto, 2) }}</flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="4" class="text-center py-6 text-zinc-500">
                                        No hay movimientos registrados.
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    @if(!$cajaSeleccionada->fecha_cierre)
                        <flux:button variant="danger" icon="lock-closed" wire:confirm="¿Estás seguro que deseas cerrar el turno actual? Esta acción no se puede deshacer." wire:click="cerrarCaja">Cerrar Turno (RF-07)</flux:button>
                    @endif
                    <flux:modal.close>
                        <flux:button variant="ghost">Cerrar Vista</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        @endif
    </flux:modal>

    {{-- MODAL 3: Registro de Ingresos/Egresos --}}
    <flux:modal name="registro-movimiento-form" class="min-w-[30rem]">
        <form wire:submit="registrarMovimiento" class="space-y-6">
            <div>
                <flux:heading size="lg">Registrar {{ ucfirst(strtolower($movimiento_tipo)) }}</flux:heading>
                <flux:subheading>Por favor indíca el monto, motivo y medio de pago.</flux:subheading>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input 
                    wire:model="movimiento_monto" 
                    type="number" 
                    step="0.01" 
                    label="Monto ($)" 
                    icon="currency-dollar" 
                    required 
                />
                
                <flux:select wire:model="movimiento_medio_pago" label="Medio de Pago" placeholder="Selecciona..." required>
                    @foreach($this->mediosPago as $mp)
                        <flux:select.option value="{{ $mp->id }}">{{ $mp->nombre }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <flux:input wire:model="movimiento_motivo" label="Motivo o Detalle" required />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Guardar Movimiento</flux:button>
            </div>
        </form>
    </flux:modal>
</div>