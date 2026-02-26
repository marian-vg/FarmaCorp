<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl max-w-none">
    {{-- Encabezado --}}
    <div class="flex justify-between items-center">
        <flux:heading level="1" size="lg">Gestión de Turnos de Caja</flux:heading>

        <flux:modal.trigger name="abrir-caja-form">
            <flux:button icon="plus" variant="primary">Nueva Apertura</flux:button>
        </flux:modal.trigger>
    </div>

    {{-- Tabla Principal --}}
    <div class="w-full overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Usuario (Credencial)</flux:table.column>
                <flux:table.column>Apertura</flux:table.column>
                <flux:table.column>Monto Inicial</flux:table.column>
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
                        <flux:table.cell colspan="5" class="text-center py-10 text-zinc-500">
                            No hay registros de caja creados todavía.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
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

                <div class="grid grid-cols-2 gap-4">
                    <flux:card>
                        <flux:text size="sm" class="mb-2 block text-zinc-500">Monto Inicial</flux:text>
                        <flux:heading size="lg">${{ number_format($cajaSeleccionada->monto_inicial, 2) }}</flux:heading>
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

                <flux:heading size="md">Movimientos de la Caja</flux:heading>
                {{-- Aquí es donde el agente luego pondrá la tabla de ingresos/egresos --}}
                <div class="p-8 border border-dashed border-zinc-300 rounded-lg text-center text-zinc-500">
                    Acá aparecerán los movimientos una vez programados.
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    @if(!$cajaSeleccionada->fecha_cierre)
                        <flux:button variant="danger" icon="lock-closed">Cerrar Turno (RF-07)</flux:button>
                    @endif
                    <flux:modal.close>
                        <flux:button variant="ghost">Cerrar Vista</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        @endif
    </flux:modal>
</div>