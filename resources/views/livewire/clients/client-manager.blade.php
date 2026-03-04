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

    <flux:modal name="view-client-modal" class="min-w-[40rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Detalles del Cliente</flux:heading>
                <flux:subheading>Información de solo lectura.</flux:subheading>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="clientContext.first_name" label="Nombres" disabled />
                <flux:input wire:model="clientContext.last_name" label="Apellidos" disabled />
                
                <flux:input wire:model="clientContext.email" type="email" label="Correo Electrónico" disabled />
                <flux:input wire:model="clientContext.phone" label="Teléfono de Contacto" disabled />
            </div>

            <flux:input wire:model="clientContext.address" label="Dirección Física" disabled />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="primary">Cerrar</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
