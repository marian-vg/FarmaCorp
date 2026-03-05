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
                            @if($cliente->saldo_pendiente > 0)
                                <flux:badge color="red" variant="solid" size="sm">
                                    ${{ number_format($cliente->saldo_pendiente, 2) }}
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
                <div class="space-y-3">
                    @forelse($this->facturasPendientes as $f)
                        <div class="flex items-center justify-between p-4 border rounded-xl dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                            <div>
                                <flux:text size="sm" class="font-bold">#{{ str_pad($f->id, 6, '0', STR_PAD_LEFT) }} - {{ $f->tipo_comprobante }}</flux:text>
                                <flux:text size="xs" class="text-zinc-500">{{ $f->fecha_emision->format('d/m/Y H:i') }}</flux:text>
                            </div>
                            <div class="flex items-center gap-4">
                                <flux:heading size="md" class="text-indigo-600">${{ number_format($f->total, 2) }}</flux:heading>
                                <flux:button 
                                    size="sm" 
                                    variant="primary" 
                                    icon="banknotes" 
                                    wire:click="cobrarFactura({{ $f->id }})" 
                                    wire:confirm="¿Confirmas que recibiste el dinero para saldar esta factura?"
                                />
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-8 text-zinc-500 italic">
                            <flux:icon.check-circle class="w-8 h-8 mb-2 opacity-20" />
                            <flux:text size="sm">Este cliente no tiene deudas pendientes.</flux:text>
                        </div>
                    @endforelse
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