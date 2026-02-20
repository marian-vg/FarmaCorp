<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

    <div class="flex justify-between items-center">
        <flux:heading level="1" size="lg">Admin Dashboard</flux:heading>

        <div class="flex justify-end items-center">
            <flux:modal.trigger name="add-user">
                <flux:button icon="plus" size="sm">New User</flux:button>
            </flux:modal.trigger>
     
            <x-add-user/>
        </div>
        
    </div>

    <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
            <thead class="bg-gray-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Usuario</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Perfil</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Permiso</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Estado</th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200 dark:bg-zinc-900 dark:divide-zinc-700">

                @foreach($users as $user)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <flux:avatar class="size-8 rounded-full" name="{{ $user->name }}" circle/>
                                <div class="flex flex-col gap-1 w-full">
                                    <flux:text>{{ $user->name }}</flux:text>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap max-48">
                            <div class="flex gap-2">
                                
                                <flux:select class="w-40" aria-label="Perfiles del usuario" size="sm">
                                    @forelse ($user->getRoleNames() as $role)
                                        <flux:select.option size="sm" value="{{ $role }}">
                                            {{ str($role) }}
                                        </flux:select.option>
                                    @empty
                                        <flux:select.option size="sm" value="" disabled selected>none</flux:select.option>
                                    @endforelse
                                </flux:select>

                                <flux:modal.trigger name="edit-profile-{{ $user->id }}">
                                    <flux:button size="sm" icon="pencil-square" variant="ghost"></flux:button>
                                </flux:modal.trigger>

                                <x-edit-profile :user="$user"/>

                            </div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex gap-2">
                                <flux:select class="w-40" aria-label="Permisos del usuario" size="sm">
                                    @forelse($user->getPermissionsViaRoles() as $permission)
                                        <flux:select.option size="sm" value="{{ $permission->name }}">
                                            {{ str($permission->name)->headline() }}
                                        </flux:select.option>
                                    @empty
                                        <flux:select.option size="sm" value="" disabled selected>
                                            none
                                        </flux:select.option>
                                    @endforelse
                                </flux:select>

                                <flux:modal.trigger name="edit-permission-{{ $user->id }}">
                                    <flux:button size="sm" icon="pencil-square" variant="ghost"></flux:button>
                                </flux:modal.trigger>
    
                                <x-edit-permission :user="$user"/>
                            </div>

                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            <flux:text>{{ $user->is_active ? 'Activo' : 'Inactivo' }}</flux:text>
                        </td>
                    </tr>
                @endforeach   

            </tbody>
        </table>
    </div>
</div>