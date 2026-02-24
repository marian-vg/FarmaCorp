<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="flex justify-between items-center">
        <flux:heading level="1" size="lg">Gestión de Perfiles</flux:heading>

        <div class="flex justify-end items-center">
            <flux:button icon="plus" wire:click="createProfile">Nuevo Perfil</flux:button>
        </div>
    </div>

    <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
            <thead class="bg-gray-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Nombre del Perfil</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Descripción</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Permisos Integrados</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Acciones</th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200 dark:bg-zinc-900 dark:divide-zinc-700">
                @forelse($this->profiles as $profile)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <flux:text class="font-medium">{{ $profile->name }}</flux:text>
                        </td>
                        <td class="px-6 py-4">
                            <flux:text class="text-sm text-gray-600 dark:text-gray-400 truncate max-w-xs">{{ $profile->description ?: 'Sin descripción' }}</flux:text>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                <flux:text class="text-sm">{{ $profile->permissions->count() }} permisos</flux:text>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex justify-end gap-2">
                                <flux:button size="sm" icon="pencil-square" variant="ghost" wire:click="editProfile({{ $profile->id }})" />
                                <flux:button size="sm" icon="trash" variant="danger" ghost wire:click="confirmDelete({{ $profile->id }})" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center">
                            <flux:text class="text-gray-500 dark:text-gray-400">No hay perfiles personalizados creados.</flux:text>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Modals -->
    <flux:modal name="profile-form" class="min-w-[32rem]">
        <form wire:submit="saveProfile" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingProfile ? 'Editar Perfil' : 'Crear Nuevo Perfil' }}</flux:heading>
                <flux:subheading>Asigna un nombre descriptivo (ej. "Caja 1") y agrupa los permisos deseados.</flux:subheading>
            </div>

            <div class="space-y-4">
                <flux:input wire:model="profileContext.name" label="Nombre del perfil" placeholder="Nombre único" required />
                <flux:textarea wire:model="profileContext.description" label="Descripción (Opcional)" placeholder="Define el propósito del perfil..." />
            </div>

            <div class="space-y-2">
                <flux:text class="font-medium mb-2 block">Permisos del Sistema</flux:text>
                <div class="grid grid-cols-2 gap-2 max-h-64 overflow-y-auto p-2 border border-solid border-gray-200 dark:border-zinc-700 rounded-md">
                    @foreach($this->permissions as $permission)
                        <flux:checkbox wire:model="selectedPermissions" value="{{ $permission->name }}" label="{{ str($permission->name)->headline() }}" />
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Guardar Perfil</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="confirm-delete-profile" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg" class="text-left text-red-600 dark:text-red-400">¿Eliminar perfil?</flux:heading>
            </div>

            <flux:text class="text-left whitespace-normal">
                Esta acción borrará el perfil <strong>{{ $editingProfile?->name }}</strong> y los permisos agrupados. Esta acción NO borrará a los usuarios, pero dejarán de heredar estos permisos inmediatamente.
            </flux:text>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button wire:click="deleteProfile" variant="danger">Eliminar para siempre</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
