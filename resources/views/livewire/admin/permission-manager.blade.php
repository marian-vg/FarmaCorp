<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="flex justify-between items-center">
        <flux:heading level="1" size="lg">Permisos del Sistema</flux:heading>

        <div class="flex items-center gap-4">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar permisos..." class="w-64" />
            <flux:button icon="plus" wire:click="createPermission" variant="primary">Nuevo Permiso</flux:button>
        </div>
    </div>

    <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
            <thead class="bg-gray-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Identificador</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Nivel de Acceso</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Descripción Funcional</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Acciones</th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200 dark:bg-zinc-900 dark:divide-zinc-700">
                @forelse($permissions as $permission)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <flux:text class="font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ $permission->name }}</flux:text>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if(str($permission->name)->contains(['user', 'role', 'permission', 'profile']))
                                <flux:badge color="red" inset="top bottom">Administrativo</flux:badge>
                            @else
                                <flux:badge color="green" inset="top bottom">Operativo</flux:badge>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <flux:text class="text-sm">{{ $permission->description ?: 'Sin descripción' }}</flux:text>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex justify-end gap-2">
                                <flux:button size="sm" icon="pencil-square" variant="ghost" wire:click="editPermission({{ $permission->id }})" />
                                <flux:button size="sm" icon="trash" variant="danger" ghost wire:click="confirmDelete({{ $permission->id }})" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center">
                            <flux:text class="text-gray-500 dark:text-gray-400">No hay permisos creados.</flux:text>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $permissions->links() }}
    </div>

    <flux:modal name="permission-form" class="min-w-[25rem]">
        <form wire:submit="savePermission" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingPermission ? 'Editar Permiso' : 'Nuevo Permiso Atómico' }}</flux:heading>
                <flux:subheading>Define un nombre técnico (ej: 'anular.venta') y una descripción para el administrador.</flux:subheading>
            </div>

            <div class="space-y-4">
                <flux:input wire:model="permissionContext.name" label="Nombre Técnico" placeholder="ej: modulo.accion" required />
                <flux:textarea wire:model="permissionContext.description" label="Descripción del permiso" placeholder="¿Qué permite hacer este permiso?" />
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Guardar</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="confirm-delete-permission" class="min-w-[22rem]">
        <div class="space-y-6">
            <flux:heading size="lg" class="text-red-600">¿Eliminar Permiso?</flux:heading>
            <flux:text>
                Eliminar el permiso <strong>{{ $editingPermission?->name }}</strong> lo quitará de todos los perfiles y usuarios que lo tengan asignado. Esta acción no se puede deshacer.
            </flux:text>
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button wire:click="deletePermission" variant="danger">Eliminar</flux:button>
            </div>
        </div>
    </flux:modal>
</div>