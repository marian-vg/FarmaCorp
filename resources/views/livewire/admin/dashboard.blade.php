<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

    <div class="flex justify-between items-center">
        <flux:heading level="1" size="lg">Admin Dashboard</flux:heading>

        <div class="flex justify-end items-center gap-4">
            <div class="flex items-center space-x-4">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar usuario..." class="w-64" />
                
                <flux:select wire:model.live="statusFilter" class="w-32" aria-label="Filtrar por estado">
                    <flux:select.option value="all">Todos</flux:select.option>
                    <flux:select.option value="active">Activos</flux:select.option>
                    <flux:select.option value="inactive">Inactivos</flux:select.option>
                </flux:select>

                <flux:select wire:model.live="roleFilter" class="w-40" aria-label="Filtrar por perfil">
                    <flux:select.option value="">Perfiles: Todos</flux:select.option>
                    @foreach($this->roles as $role)
                        <flux:select.option value="{{ $role->name }}">{{ str($role->name)->headline() }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <flux:modal.trigger name="add-user">
                <flux:button icon="plus">New User</flux:button>
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
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Acciones</th>
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
                                
                                <flux:select class="w-40" aria-label="Perfiles del usuario" size="sm" disabled>
                                    @forelse ($user->roles as $role)
                                        <flux:select.option size="sm" value="{{ $role->name }}">
                                            {{ str($role->name)->headline() }}
                                        </flux:select.option>
                                    @empty
                                        <flux:select.option size="sm" value="" disabled selected>Sin rol</flux:select.option>
                                    @endforelse
                                </flux:select>

                                <flux:button size="sm" icon="pencil-square" variant="ghost" wire:click="editRoles({{ $user->id }})" />
                            </div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex gap-2">
                                <flux:select class="w-40" aria-label="Permisos del usuario" size="sm" disabled>
                                    @forelse($user->getDirectPermissions() as $permission)
                                        <flux:select.option size="sm" value="{{ $permission->name }}">
                                            {{ str($permission->name)->headline() }}
                                        </flux:select.option>
                                    @empty
                                        <flux:select.option size="sm" value="" disabled selected>Sin permisos directos</flux:select.option>
                                    @endforelse
                                </flux:select>

                                <flux:button size="sm" icon="pencil-square" variant="ghost" wire:click="editPermissions({{ $user->id }})" />
                            </div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex gap-2">
                                <flux:select class="w-40" aria-label="Perfiles Personalizados" size="sm" disabled>
                                    @forelse($user->profiles as $profile)
                                        <flux:select.option size="sm" value="{{ $profile->id }}">
                                            {{ str($profile->name)->headline() }}
                                        </flux:select.option>
                                    @empty
                                        <flux:select.option size="sm" value="" disabled selected>Sin perfiles</flux:select.option>
                                    @endforelse
                                </flux:select>

                                <flux:button size="sm" icon="pencil-square" variant="ghost" wire:click="editProfiles({{ $user->id }})" />
                            </div>

                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            <flux:badge color="{{ $user->is_active ? 'green' : 'red' }}" inset="top bottom">{{ $user->is_active ? 'Activo' : 'Inactivo' }}</flux:badge>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex justify-end gap-2">
                                <flux:modal.trigger name="reset-password-{{ $user->id }}">
                                    <flux:button size="sm" icon="key" variant="ghost" />
                                </flux:modal.trigger>
                                
                                @if($user->is_active)
                                    <flux:modal.trigger name="confirm-deactivation-{{ $user->id }}">
                                        <flux:button size="sm" icon="trash" variant="danger" ghost />
                                    </flux:modal.trigger>
                                @else
                                    <flux:button size="sm" icon="arrow-path" variant="subtle" wire:click="reactivateUser({{ $user->id }})" />
                                @endif

                                <flux:modal name="reset-password-{{ $user->id }}" class="min-w-[22rem]">
                                    <div class="space-y-6">
                                        <div>
                                            <flux:heading size="lg" class="text-left">Resetear Contraseña</flux:heading>
                                            <flux:subheading class="text-left">Actualizar contraseña de {{ $user->name }}.</flux:subheading>
                                        </div>

                                        <flux:input wire:model="newPassword" type="password" label="Nueva contraseña" placeholder="Ingresa la nueva contraseña" />
                                        <flux:input wire:model="newPasswordConfirmation" type="password" label="Confirmar contraseña" placeholder="Confirma la contraseña" />

                                        <div class="flex justify-end">
                                            <flux:modal.close>
                                                <flux:button wire:click="updatePassword({{ $user->id }})" variant="primary">Guardar Contraseña</flux:button>
                                            </flux:modal.close>
                                        </div>
                                    </div>
                                </flux:modal>

                                <flux:modal name="confirm-deactivation-{{ $user->id }}" class="min-w-[22rem]">
                                    <div class="space-y-6">
                                        <div>
                                            <flux:heading size="lg" class="text-left">¿Desactivar usuario?</flux:heading>
                                        </div>

                                        <flux:text class="text-left whitespace-normal">
                                            ¿Estás seguro? El usuario <strong>{{ $user->name }}</strong> será marcado como INACTIVO en el sistema y no podrá iniciar sesión, pero su historial de operaciones se mantendrá intacto.
                                        </flux:text>

                                        <div class="flex justify-end gap-2">
                                            <flux:modal.close>
                                                <flux:button variant="ghost">Cancelar</flux:button>
                                            </flux:modal.close>
                                            
                                            <flux:modal.close>
                                                <flux:button wire:click="deactivateUser({{ $user->id }})" variant="danger">Desactivar</flux:button>
                                            </flux:modal.close>
                                        </div>
                                    </div>
                                </flux:modal>
                            </div>
                        </td>
                    </tr>
                @endforeach   

            </tbody>
        </table>
    </div>

    <!-- Centralized Modals -->
    <flux:modal name="edit-roles" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Roles del Usuario</flux:heading>
                <flux:subheading>Asignar roles base al usuario actual.</flux:subheading>
            </div>

            <div class="space-y-2 max-h-60 overflow-y-auto">
                @foreach($this->roles as $role)
                    <flux:checkbox wire:model="selectedRoles" value="{{ $role->name }}" label="{{ str($role->name)->headline() }}" />
                @endforeach
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="saveRoles">Guardar Cambios</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="edit-permissions" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Permisos Directos</flux:heading>
                <flux:subheading>Asignar permisos excepcionales directos.</flux:subheading>
            </div>

            <div class="space-y-2 max-h-60 overflow-y-auto">
                @foreach($this->permissions as $permission)
                    <flux:checkbox wire:model="selectedPermissions" value="{{ $permission->name }}" label="{{ str($permission->name)->headline() }}" />
                @endforeach
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="savePermissions">Guardar Cambios</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="edit-profiles" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Perfiles Activos</flux:heading>
                <flux:subheading>Asignar perfiles para otorgar sus permisos al usuario.</flux:subheading>
            </div>

            <div class="space-y-2 max-h-60 overflow-y-auto">
                @foreach($this->allProfiles as $profile)
                    <flux:checkbox wire:model="selectedProfiles" value="{{ $profile->id }}" label="{{ str($profile->name)->headline() }}" />
                @endforeach
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="saveProfiles">Guardar Cambios</flux:button>
            </div>
        </div>
    </flux:modal>

</div>