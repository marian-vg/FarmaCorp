<div class="space-y-6">
    {{-- ENCABEZADO Y RESUMEN --}}
    <div class="flex justify-between items-center">
        <div>
            <flux:heading size="xl">Saldos de Cuentas Corrientes (RF-16)</flux:heading>
            <flux:subheading>Monitoreo de deudas y saldos pendientes por cliente.</flux:subheading>
        </div>
        
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4 rounded-xl">
            <flux:text size="sm" class="text-red-700 dark:text-red-400 uppercase font-bold">Total a Cobrar</flux:text>
            <flux:heading size="xl" class="text-red-600">${{ number_format($this->totalEnLaCalle, 2) }}</flux:heading>
        </div>
    </div>

    {{-- BUSCADOR --}}
    <div class="flex gap-4">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar cliente por nombre o teléfono..." class="flex-1" />
    </div>

    {{-- TABLA DE CLIENTES --}}
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
                            @if($cliente->saldo_real_pendiente > 0)
                                <flux:badge color="red" variant="solid" size="sm">
                                    ${{ number_format($cliente->saldo_real_pendiente, 2) }}
                                </flux:badge>
                            @else
                                <flux:badge color="green" variant="subtle" size="sm">Al día</flux:badge>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell align="end">
                            <flux:button 
                                icon="eye" 
                                size="xs" 
                                variant="ghost" 
                                wire:click="verDetalleDeuda({{ $cliente->id }})" 
                                tooltip="Ver deudas y cobrar" 
                            />
                        </flux:table.cell>
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

    {{-- EL MODAL AHORA ESTÁ FUERA DE LA TABLA (Solución al error del ojo) --}}
    <flux:modal name="cobro-modal" class="md:w-6/12">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Gestión de Cuenta Corriente</flux:heading>
                <flux:subheading>Revise las deudas o el historial de pagos del cliente.</flux:subheading>
            </div>

            {{-- Navegación de pestañas interna (RF-24) --}}
            <div class="flex gap-4 border-b border-zinc-200 dark:border-zinc-700">
                <button wire:click="$set('modalTab', 'pendientes')" 
                    class="pb-2 text-sm font-medium transition-colors {{ $modalTab === 'pendientes' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-zinc-500 hover:text-zinc-700' }}">
                    Deudas Pendientes ({{ count($this->facturasPendientes) }})
                </button>
                <button wire:click="$set('modalTab', 'historial')" 
                    class="pb-2 text-sm font-medium transition-colors {{ $modalTab === 'historial' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-zinc-500 hover:text-zinc-700' }}">
                    Historial de Pagos
                </button>
            </div>

            {{-- CONTENIDO PESTAÑA PENDIENTES (RF-16) --}}
            @if($modalTab === 'pendientes')
                <div class="space-y-4">
                    @if(!$facturaEnCobro)
                        {{-- LISTA DE FACTURAS --}}
                        <div class="space-y-3">
                            @forelse($this->facturasPendientes as $f)
                                @php 
                                    $yaPagado = $f->pagos->sum('monto');
                                    $saldoActual = $f->total - $yaPagado;
                                @endphp
                                <div class="flex items-center justify-between p-4 border rounded-xl dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                                    <div>
                                        <flux:text size="sm" class="font-bold">#{{ str_pad($f->id, 6, '0', STR_PAD_LEFT) }}</flux:text>
                                        <flux:text size="xs" class="text-zinc-500">Total: ${{ number_format($f->total, 2) }}</flux:text>
                                        @if($yaPagado > 0)
                                            <flux:text size="xs" class="text-green-600 font-medium">Entregó: ${{ number_format($yaPagado, 2) }}</flux:text>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <div class="text-right">
                                            <flux:text size="xs" class="uppercase text-zinc-400 font-bold">Saldo:</flux:text>
                                            <flux:heading size="md" class="text-red-600">${{ number_format($saldoActual, 2) }}</flux:heading>
                                        </div>
                                        <flux:button size="sm" variant="subtle" icon="plus-circle" wire:click="seleccionarFacturaParaCobro({{ $f->id }})" />
                                    </div>
                                </div>
                            @empty
                                <flux:text size="sm" class="text-center py-8">No hay deudas pendientes.</flux:text>
                            @endforelse
                        </div>
                    @else
                        {{-- INTERFAZ DE COBRO MULTIMEDIO (REPLICA VENTA MANAGER) --}}
                        <div class="p-4 border-2 border-indigo-100 dark:border-indigo-900/30 rounded-2xl bg-indigo-50/30 dark:bg-indigo-900/10 space-y-4">
                            <div class="flex justify-between items-center">
                                <flux:heading size="md">Cobrando Factura #{{ str_pad($facturaEnCobro->id, 6, '0', STR_PAD_LEFT) }}</flux:heading>
                                <flux:button size="xs" variant="ghost" wire:click="cancelarCobro">Cambiar factura</flux:button>
                            </div>

                            {{-- Inputs de pago --}}
                            @if($this->montoRestanteFactura > 0.01)
                                <div class="flex gap-2 items-end">
                                    <div class="flex-1">
                                        <flux:select wire:model.live="medio_pago_id" label="Medio">
                                            <option value="">Elegir...</option>
                                            @foreach($mediosPago as $mp)
                                                <option value="{{ $mp->id }}">{{ $mp->nombre }}</option>
                                            @endforeach
                                        </flux:select>
                                    </div>

                                    <div class="w-32">
                                        <flux:input wire:model.live="monto_pago_actual" type="number" label="Monto" />
                                    </div>

                                    {{-- BOTÓN RÁPIDO (EL RAYITO) --}}
                                    <flux:button 
                                        icon="bolt" 
                                        variant="ghost" 
                                        wire:click="autocompletarMonto" 
                                        class="mb-0.5" 
                                        tooltip="Usar saldo total" 
                                    />

                                    <flux:button icon="plus" variant="subtle" wire:click="agregarPago" class="mb-0.5" />
                                </div>
                                @error('monto_pago_actual')
                                    <flux:text size="xs" class="text-red-500 font-medium">{{ $message }}</flux:text>
                                @enderror
                            @endif

                            {{-- Lista de pagos parciales --}}
                            <div class="space-y-2">
                                @foreach($pagos_acumulados as $index => $pago)
                                    <div wire:key="pago-debito-{{ $index }}" class="flex justify-between items-center p-2 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                        <flux:text size="xs"><strong>{{ $pago['nombre'] }}:</strong> ${{ number_format($pago['monto'], 2) }}</flux:text>
                                        <flux:button icon="x-mark" size="xs" variant="ghost" wire:click="quitarPago({{ $index }})" />
                                    </div>
                                @endforeach
                            </div>

                            {{-- Balance y Botón Final --}}
                            <div class="flex justify-between items-center pt-2">
                                <div class="flex flex-col">
                                    <flux:text size="xs" class="uppercase font-bold text-zinc-500">Saldo Restante:</flux:text>
                                    <flux:heading size="lg" class="{{ $this->montoRestanteFactura <= 0.01 ? 'text-green-600' : 'text-orange-600' }}">
                                        ${{ number_format($this->montoRestanteFactura, 2) }}
                                    </flux:heading>
                                </div>
                                <flux:button 
                                    variant="primary" 
                                    wire:click="cobrarFactura" 
                                    icon="check-circle"
                                    :disabled="$this->montoRestanteFactura > 0.01"
                                >Confirmar Cobro</flux:button>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- CONTENIDO PESTAÑA HISTORIAL (RF-24) --}}
            @if($modalTab === 'historial')
                <div class="space-y-3">
                    @forelse($this->facturasPagadas as $f)
                        <div class="flex items-center justify-between p-4 border border-dashed rounded-xl dark:border-zinc-800">
                            <div>
                                <flux:text size="sm" class="font-medium">#{{ str_pad($f->id, 6, '0', STR_PAD_LEFT) }} - {{ $f->tipo_comprobante }}</flux:text>
                                <flux:text size="xs" class="text-zinc-500">Pagado el {{ $f->updated_at->format('d/m/Y H:i') }}</flux:text>
                            </div>
                            <div class="text-right">
                                <flux:text size="sm" class="font-bold text-green-600">${{ number_format($f->total, 2) }}</flux:text>
                                <flux:badge size="xs" color="green" variant="subtle">Saldado</flux:badge>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-8 text-zinc-500 italic">
                            <flux:icon.document-text class="w-8 h-8 mb-2 opacity-20" />
                            <flux:text size="sm">No hay registros de pagos anteriores.</flux:text>
                        </div>
                    @endforelse
                </div>
            @endif

            <div class="flex justify-end pt-4">
                <flux:modal.close>
                    <flux:button variant="ghost">Cerrar</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>