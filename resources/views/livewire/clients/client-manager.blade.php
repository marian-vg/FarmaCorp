<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="flex justify-between items-center">
        <flux:heading level="1" size="lg">Directorio de Clientes</flux:heading>

        <div class="flex items-center gap-4">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar por nombre o teléfono..." class="w-64" />
            <flux:select wire:model.live="statusFilter" class="w-40">
                <flux:select.option value="all">Todos</flux:select.option>
                <flux:select.option value="active">Activos</flux:select.option>
                <flux:select.option value="inactive">Inactivos</flux:select.option>
            </flux:select>
            @hasanyrole('admin|empleado')
                <flux:button icon="plus" wire:click="createClient" variant="primary">Nuevo Cliente</flux:button>
            @endhasanyrole
        </div>
    </div>

    <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
            <thead class="bg-gray-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Cliente</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Contacto</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Dirección</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Estado</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Acciones</th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200 dark:bg-zinc-900 dark:divide-zinc-700">
                @forelse($clients as $client)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <flux:text class="font-medium">{{ $client->first_name }} {{ $client->last_name }}</flux:text>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $client->email ?: 'Sin correo' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-500">{{ $client->phone }}</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <flux:text class="text-sm truncate max-w-xs">{{ $client->address }}</flux:text>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($client->is_active)
                                <flux:badge color="green" size="sm" inset="top bottom">Activo</flux:badge>
                            @else
                                <flux:badge color="red" size="sm" inset="top bottom">Inactivo</flux:badge>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex justify-end gap-2">
                                <flux:button size="sm" icon="eye" variant="ghost" wire:click="viewClient({{ $client->id }})" />
                                
                                @hasanyrole('admin|empleado')
                                    <flux:button size="sm" icon="pencil-square" variant="ghost" wire:click="editClient({{ $client->id }})" />
                                    @if($client->is_active)
                                        <flux:button size="sm" icon="trash" variant="danger" ghost wire:click="confirmDeactivate({{ $client->id }})" />
                                    @else
                                        <flux:button size="sm" icon="arrow-path" variant="subtle" wire:click="reactivateClient({{ $client->id }})" />
                                    @endif
                                @endhasanyrole
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center">
                            <flux:text class="text-gray-500 dark:text-gray-400">No se encontraron clientes.</flux:text>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $clients->links() }}
    </div>

    <!-- Modals -->
    <flux:modal name="client-form" class="min-w-[40rem]">
        <form wire:submit="saveClient" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingClient ? 'Editar Información del Cliente' : 'Registrar Nuevo Cliente' }}</flux:heading>
                <flux:subheading>Datos obligatorios para facturación y seguimiento básico.</flux:subheading>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="clientContext.first_name" label="Nombres" required />
                <flux:input wire:model="clientContext.last_name" label="Apellidos" required />
                
                <flux:input wire:model="clientContext.email" type="email" label="Correo Electrónico" />
                <flux:input wire:model="clientContext.phone" label="Teléfono de Contacto" required />
            </div>

            <flux:input wire:model="clientContext.address" label="Dirección Física" required />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Guardar Cliente</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="confirm-deactivation-client" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg" class="text-left text-red-600 dark:text-red-400">¿Desactivar cliente?</flux:heading>
            </div>

            <flux:text class="text-left whitespace-normal">
                Esta acción marcará a <strong>{{ $editingClient?->first_name }} {{ $editingClient?->last_name }}</strong> como inactivo. Conservará su historial pero dejará de estar disponible para ser asociado a nuevas facturas.
            </flux:text>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Mantener Activo</flux:button>
                </flux:modal.close>
                <flux:button wire:click="deactivateClient" variant="danger">Desactivar</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="view-client-modal" class="min-w-[45rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Expediente del Cliente</flux:heading>
                <flux:subheading>Consulta de información y actividad comercial.</flux:subheading>
            </div>

            {{-- Navegación de pestañas interna --}}
            <div class="flex gap-4 border-b border-zinc-200 dark:border-zinc-700">
                <button wire:click="$set('modalTab', 'info')" 
                    class="pb-2 text-sm font-medium transition-colors {{ $modalTab === 'info' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-zinc-500 hover:text-zinc-700' }}">
                    Información Básica
                </button>
                <button wire:click="$set('modalTab', 'compras')" 
                    class="pb-2 text-sm font-medium transition-colors {{ $modalTab === 'compras' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-zinc-500 hover:text-zinc-700' }}">
                    Historial de Compras (RF-24)
                </button>
            </div>

            {{-- CONTENIDO PESTAÑA: INFO --}}
            @if($modalTab === 'info')
                <div class="grid grid-cols-2 gap-4 animate-fade-in">
                    <flux:input wire:model="clientContext.first_name" label="Nombres" disabled />
                    <flux:input wire:model="clientContext.last_name" label="Apellidos" disabled />
                    <flux:input wire:model="clientContext.email" type="email" label="Correo Electrónico" disabled />
                    <flux:input wire:model="clientContext.phone" label="Teléfono" disabled />
                    <div class="col-span-2">
                        <flux:input wire:model="clientContext.address" label="Dirección Física" disabled />
                    </div>
                </div>
            @endif

            {{-- CONTENIDO PESTAÑA: HISTORIAL (RF-24) --}}
            @if($modalTab === 'compras')
                <div class="space-y-3 max-h-[30rem] overflow-y-auto pr-2">
                    @forelse($this->historialCompras as $f)
                        <div class="flex items-center justify-between p-3 border border-zinc-200 dark:border-zinc-800 rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <div class="flex flex-col">
                                <flux:text size="sm" class="font-bold">#{{ str_pad($f->id, 6, '0', STR_PAD_LEFT) }} - {{ $f->tipo_comprobante }}</flux:text>
                                <flux:text size="xs" class="text-zinc-500">{{ $f->fecha_emision->format('d/m/Y H:i') }}</flux:text>
                                <flux:badge size="xs" :color="$f->estado === 'PENDIENTE' ? 'yellow' : 'green'" class="w-fit mt-1">
                                    {{ $f->estado }}
                                </flux:badge>
                            </div>
                            
                            <div class="flex items-center gap-3">
                                <flux:text size="sm" class="font-bold text-indigo-600">${{ number_format($f->total, 2) }}</flux:text>
                                <div class="flex gap-1">
                                    <flux:button icon="eye" size="xs" variant="ghost" wire:click="verDetalleFactura({{ $f->id }})" />
                                    <flux:button icon="document-arrow-down" size="xs" variant="ghost" wire:click="descargarFactura({{ $f->id }})" class="text-indigo-600" />
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 text-zinc-500 italic">Este cliente aún no ha realizado compras.</div>
                    @endforelse
                </div>
            @endif

            <div class="flex justify-end pt-4 border-t">
                <flux:modal.close><flux:button variant="primary">Cerrar</flux:button></flux:modal.close>
            </div>
        </div>
    </flux:modal>
    <flux:modal name="detalle-auditoria-modal" class="min-w-[40rem]">
    @if($facturaSeleccionada)
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Detalle de Compra #{{ str_pad($facturaSeleccionada->id, 6, '0', STR_PAD_LEFT) }}</flux:heading>
                <flux:subheading>{{ $facturaSeleccionada->tipo_comprobante }} | Atendido por: {{ $facturaSeleccionada->user->name }}</flux:subheading>
            </div>

            <flux:separator />
            
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Producto</flux:table.column>
                    <flux:table.column>Cant.</flux:table.column>
                    <flux:table.column align="end">Subtotal</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($facturaSeleccionada->details as $item)
                        <flux:table.row>
                            <flux:table.cell>{{ $item->product->name }}</flux:table.cell>
                            <flux:table.cell>{{ $item->cantidad }}</flux:table.cell>
                            <flux:table.cell align="end" class="font-medium">${{ number_format($item->cantidad * $item->precio_unitario, 2) }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            {{-- SECCIÓN DE PAGOS --}}
            @if($facturaSeleccionada->pagos->isNotEmpty())
                <div class="space-y-2">
                    <flux:heading size="sm" class="text-zinc-500 uppercase tracking-wider">Flujo de Fondos</flux:heading>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($facturaSeleccionada->pagos->groupBy('id_medio_pago') as $idMedio => $grupoPagos)
                            <div class="flex justify-between items-center text-xs p-3 bg-zinc-50 dark:bg-zinc-800/50 border rounded-xl border-zinc-200">
                                <span class="font-medium">{{ $grupoPagos->first()->medioPago->nombre }}</span>
                                <span class="font-bold text-green-600">${{ number_format($grupoPagos->sum('monto'), 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="space-y-2 p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200">
                <div class="flex justify-between items-center py-2 font-bold">
                    <flux:text class="uppercase">Total Operación:</flux:text>
                    <flux:heading size="lg" class="text-indigo-600">${{ number_format($facturaSeleccionada->total, 2) }}</flux:heading>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Cerrar</flux:button></flux:modal.close>
                <flux:button variant="primary" icon="document-arrow-down" wire:click="descargarFactura({{ $facturaSeleccionada->id }})">Descargar PDF</flux:button>
            </div>
        </div>
    @endif
</flux:modal>
</div>
