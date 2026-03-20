<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <flux:heading level="1" size="lg">Panel de Administración</flux:heading>
    
    <div class="grid grid-cols-1 gap-4 mb-2 mt-4">
        <div class="w-full bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl p-6 shadow-lg shadow-zinc-800/25">
            <div>
                <flux:heading size="lg">Configuración de Alertas</flux:heading>
                <flux:subheading>Define con cuántos días de anticipación deseas ver los medicamentos próximos a vencer.</flux:subheading>
            </div>
    
            <form wire:submit.prevent="saveAlertDays" class="mt-4 flex items-end gap-4">
                <flux:input type="number" wire:model="alertDays" label="Período de anticipación (en días)" min="1" max="365" class="w-48" />
                <flux:button type="submit" variant="primary">Guardar Configuración</flux:button>
            </form>
    
            <div class="mt-6 w-full overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700 ">
                <x-table>
                    <x-table.head>
                        <x-table.heading>Medicamento</x-table.heading>
                        <x-table.heading>Lote</x-table.heading>
                        <x-table.heading>Vencimiento</x-table.heading>
                        <x-table.heading>Restante</x-table.heading>
                    </x-table.head>
                    <x-table.body>
                        @forelse($expiringBatches as $batch)
                            @php
                                $expireDate = \Carbon\Carbon::parse($batch->expiration_date)->startOfDay();
                                $daysLeft = (int) now()->startOfDay()->diffInDays($expireDate, false);
                                $isCritical = $daysLeft <= 15; // critical if 15 days or less
                            @endphp
                            <x-table.row class="{{ $isCritical ? 'bg-red-50 dark:bg-red-900/20' : ($daysLeft <= 30 ? 'bg-yellow-50 dark:bg-yellow-900/20' : '') }}">
                                <x-table.cell class="font-medium {{ $isCritical ? 'text-red-700 dark:text-red-400' : 'text-zinc-900 dark:text-zinc-100' }}">{{ $batch->medicine->product?->name ?? 'N/D' }}</x-table.cell>
                                <x-table.cell class="text-zinc-500 dark:text-zinc-400">{{ $batch->batch_number }}</x-table.cell>
                                <x-table.cell class="{{ $isCritical ? 'text-red-600 dark:text-red-400 font-bold' : ($daysLeft <= 30 ? 'text-orange-600 dark:text-orange-400 font-bold' : 'text-zinc-500 dark:text-zinc-400') }}">
                                    {{ \Carbon\Carbon::parse($batch->expiration_date)->format('d/m/Y') }}
                                    <span class="ml-2 text-xs">({{ $daysLeft > 0 ? "en $daysLeft días" : ($daysLeft === 0 ? 'hoy' : 'vencido') }})</span>
                                </x-table.cell>
                                <x-table.cell class="text-sm text-zinc-500 dark:text-zinc-400">
                                    <flux:badge variant="solid" color="zinc">{{ $batch->current_quantity }}</flux:badge>
                                </x-table.cell>
                            </x-table.row>
                        @empty
                            <x-table.row>
                                <x-table.cell colspan="4" class="text-zinc-500 dark:text-zinc-400 text-center">
                                    No hay medicamentos próximos a vencer en los próximos {{ $alertDays }} días.
                                </x-table.cell>
                            </x-table.row>
                        @endforelse
                    </x-table.body>
                </x-table>
            </div>
        </div>
    </div>

    <div class="mt-8 border-t pt-6">
        <flux:heading size="lg">Control de Inflación</flux:heading>
        <flux:subheading>Define la antigüedad máxima permitida para el precio de un producto antes de bloquear su venta.</flux:subheading>
        
        <form wire:submit.prevent="savePriceConfig" class="mt-4 flex items-end gap-4">
            <flux:input type="number" wire:model="priceMaxDays" label="Días de validez del precio" class="w-48" />
            <flux:button type="submit" variant="primary">Guardar Límite</flux:button>
        </form>
    </div>

    <div class="mt-8 border-t pt-6">
        <flux:heading size="lg">Comportamiento de Cierre</flux:heading>
        <flux:subheading>Define qué sucede automáticamente al confirmar una venta.</flux:subheading>
        
        <form wire:submit.prevent="saveSaleConfig" class="mt-4 space-y-4">
            <flux:radio.group wire:model="postSaleAction" label="Acción al cerrar comprobante">
                <flux:radio value="solo_guardar" label="Solo Guardar (Sin descargar)" />
                <flux:radio value="auto_imprimir" label="Guardar e Imprimir Automáticamente" />
                <flux:radio value="preguntar" label="Preguntar al finalizar" />
            </flux:radio.group>
            <flux:button type="submit" variant="primary">Guardar Preferencia</flux:button>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-4 mb-6">
        <div class="w-full">
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl p-6 shadow-lg shadow-zinc-800/25">
                <div class="flex items-center space-x-2 mb-4">
                    <flux:icon.exclamation-triangle variant="outline" class="w-5 h-5 text-orange-500" />
                    <flux:heading size="lg">Quiebre de Stock (Mínimos)</flux:heading>
                </div>
                <div class="overflow-x-auto w-full overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700">
                    <x-table>
                        <x-table.head>
                            <x-table.heading>Medicamento</x-table.heading>
                            <x-table.heading>Lote</x-table.heading>
                            <x-table.heading class="text-right">C. Actual</x-table.heading>
                            <x-table.heading class="text-right">C. Mínimo</x-table.heading>
                        </x-table.head>
                        <x-table.body>
                            @forelse ($lowStockBatches as $batch)
                                <x-table.row>
                                    <x-table.cell class="font-medium text-zinc-900 dark:text-zinc-100">
                                        {{ $batch->medicine?->product?->name ?? 'N/D' }}
                                    </x-table.cell>
                                    <x-table.cell class="text-zinc-500 dark:text-zinc-400">
                                        {{ $batch->batch_number }}
                                    </x-table.cell>
                                    <x-table.cell class="text-right font-bold text-orange-600">
                                        {{ $batch->current_quantity }}
                                    </x-table.cell>
                                    <x-table.cell class="text-right text-zinc-500 dark:text-zinc-400">
                                        {{ $batch->minimum_stock }}
                                    </x-table.cell>
                                </x-table.row>
                            @empty
                                <x-table.row>
                                    <x-table.cell colspan="4" class="text-center text-zinc-500 dark:text-zinc-400">
                                        Todos los lotes se encuentran por encima de su stock mínimo.
                                    </x-table.cell>
                                </x-table.row>
                            @endforelse
                        </x-table.body>
                    </x-table>
                </div>
            </div>
        </div>
    </div>

    <div class="flex justify-between items-center">
        

        <div class="flex justify-end items-center gap-4">
            <div class="flex items-center space-x-4">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar usuario..." class="w-64" />
                
                <flux:select wire:model.live="statusFilter" class="w-32" aria-label="Filtrar por estado">
                    <flux:select.option value="all">Todos</flux:select.option>
                    <flux:select.option value="active">Activos</flux:select.option>
                    <flux:select.option value="inactive">Inactivos</flux:select.option>
                </flux:select>

                <flux:select wire:model.live="profileFilter" class="w-40" aria-label="Filtrar por perfil">
                    <flux:select.option value="">Perfiles: Todos</flux:select.option>
                    @foreach($this->allProfiles as $profile)
                        <flux:select.option value="{{ $profile->name }}">{{ str($profile->name)->headline() }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <flux:modal.trigger name="add-user">
                <flux:button icon="plus">Nuevo usuario</flux:button>
            </flux:modal.trigger>
     
            <x-add-user/>
        </div>
        
    </div>

    <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700 shadow-lg shadow-zinc-800/25">
        <x-table>
            <x-table.head>
                <x-table.heading>Usuario</x-table.heading>
                <x-table.heading>Rol</x-table.heading>
                <x-table.heading>Permisos</x-table.heading>
                <x-table.heading>Perfiles</x-table.heading>
                <x-table.heading>Estado</x-table.heading>
                <x-table.heading class="text-right">Acciones</x-table.heading>
            </x-table.head>
            <x-table.body>
                @foreach($users as $user)
                    <x-table.row wire:key="user-row-{{ $user->id }}">
                        <x-table.cell>
                            <div class="flex items-center gap-3">
                                <flux:avatar class="size-8 rounded-full" name="{{ $user->name }}" circle/>
                                <div class="flex flex-col gap-1 w-full">
                                    <flux:text>{{ $user->name }}</flux:text>
                                </div>
                            </div>
                        </x-table.cell>

                        <x-table.cell class="max-48">
                            <div class="flex gap-2">
                                
                                <flux:dropdown>
                                    <flux:button size="sm" class="w-40 justify-between items-center text-left" icon-trailing="chevron-down">
                                        {{ $user->roles->count() }} Rol(es)
                                    </flux:button>
                                    <flux:menu class="max-h-48 overflow-y-auto">
                                        @forelse ($user->roles as $role)
                                            <flux:menu.item wire:key="user-{{ $user->id }}-role-{{ $role->id }}">
                                                {{ str($role->name)->headline() }}
                                            </flux:menu.item>
                                        @empty
                                            <flux:menu.item disabled>Sin roles</flux:menu.item>
                                        @endforelse
                                    </flux:menu>
                                </flux:dropdown>

                                <flux:button size="sm" icon="pencil-square" variant="ghost" wire:click="editRoles({{ $user->id }})" wire:key="btn-roles-{{ $user->id }}" />
                            </div>
                        </x-table.cell>

                        <x-table.cell>
                            <div class="flex gap-2">
                                @php
                                    $allUserPermissions = $user->getAllEffectivePermissions();
                                @endphp
                                <flux:dropdown>
                                    <flux:button size="sm" class="w-40 justify-between items-center text-left" icon-trailing="chevron-down">
                                        {{ $allUserPermissions->count() }} Permiso(s)
                                    </flux:button>
                                    <flux:menu class="max-h-48 overflow-y-auto">
                                        @forelse($allUserPermissions as $permission)
                                            <flux:menu.item wire:key="user-{{ $user->id }}-perm-{{ $permission->id }}">
                                                {{ str($permission->display_name ?? $permission->name)->headline() }}
                                            </flux:menu.item>
                                        @empty
                                            <flux:menu.item disabled>Ningún Permiso</flux:menu.item>
                                        @endforelse
                                    </flux:menu>
                                </flux:dropdown>

                                <flux:button size="sm" icon="pencil-square" variant="ghost" wire:click="editPermissions({{ $user->id }})" wire:key="btn-perms-{{ $user->id }}"/>
                            </div>
                        </x-table.cell>

                        <x-table.cell>
                            <div class="flex gap-2">
                                <flux:dropdown>
                                    <flux:button size="sm" class="w-40 justify-between items-center text-left" icon-trailing="chevron-down">
                                        {{ $user->profiles->count() }} Perfil(es)
                                    </flux:button>
                                    <flux:menu class="max-h-48 overflow-y-auto w-40">
                                        @forelse($user->profiles as $profile)
                                            <flux:menu.item wire:key="user-{{ $user->id }}-prof-{{ $profile->id }}">
                                                {{ str($profile->name)->headline() }}
                                            </flux:menu.item>
                                        @empty
                                            <flux:menu.item disabled>Sin extras</flux:menu.item>
                                        @endforelse
                                    </flux:menu>
                                </flux:dropdown>

                                <flux:button size="sm" icon="pencil-square" variant="ghost" wire:click="editProfiles({{ $user->id }})" wire:key="btn-profs-{{ $user->id }}"/>
                            </div>
                        </x-table.cell>

                        <x-table.cell>
                            <flux:badge color="{{ $user->is_active ? 'green' : 'red' }}" inset="top bottom">{{ $user->is_active ? 'Activo' : 'Inactivo' }}</flux:badge>
                        </x-table.cell>

                        <x-table.cell class="text-right">
                            <div class="flex justify-end gap-2">
                                <flux:button size="sm" icon="pencil-square" variant="ghost" wire:click="editUser({{ $user->id }})" wire:key="btn-edit-{{ $user->id }}"/>
                                <flux:modal.trigger name="reset-password-{{ $user->id }}">
                                    <flux:button size="sm" icon="key" variant="ghost" />
                                </flux:modal.trigger>
                                
                                @if($user->is_active)
                                    @if($user->id !== auth()->id())
                                        <flux:modal.trigger name="confirm-deactivation-{{ $user->id }}">
                                            <flux:button size="sm" icon="trash" variant="danger" ghost />
                                        </flux:modal.trigger>
                                    @endif
                                @else
                                    <flux:button size="sm" icon="arrow-path" variant="subtle" wire:click="reactivateUser({{ $user->id }})" wire:key="btn-reactivate-{{ $user->id }}"/>
                                @endif

                                <flux:modal name="reset-password-{{ $user->id }}" class="min-w-xs">
                                    <div class="space-y-6 flex flex-col items-start">
                                        <div>
                                            <flux:heading size="lg" class="text-left">Resetear Contraseña</flux:heading>
                                            <flux:subheading class="text-left">Actualizar contraseña de {{ $user->name }}.</flux:subheading>
                                        </div>

                                        <flux:input wire:model="newPassword" type="password" label="Nueva contraseña" placeholder="Ingresa la nueva contraseña" />
                                        <flux:input wire:model="newPasswordConfirmation" type="password" label="Confirmar contraseña" placeholder="Confirma la contraseña" />

                                        <div class="flex flex-row justify-end w-full">
                                            <flux:modal.close>
                                                <flux:button wire:click="updatePassword({{ $user->id }})" variant="primary">Guardar Contraseña</flux:button>
                                            </flux:modal.close>
                                        </div>
                                    </div>
                                </flux:modal>

                                <flux:modal name="confirm-deactivation-{{ $user->id }}" class="min-w-xs">
                                    <div class="space-y-6">
                                        <div>
                                            <flux:heading size="lg" class="text-left">¿Desactivar usuario?</flux:heading>
                                        </div>

                                        <flux:text class="text-left whitespace-normal">
                                            ¿Estás seguro? El usuario <strong>{{ $user->name }}</strong> no podrá iniciar sesión.
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
                        </x-table.cell>
                    </x-table.row>
                @endforeach   
            </x-table.body>
        </x-table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>

    <flux:modal name="edit-user" class="min-w-160">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Editar Usuario</flux:heading>
                <flux:subheading>Actualiza la información básica, estado, perfiles y permisos directos del usuario.</flux:subheading>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="editUserContext.name" label="Nombre completo" required />
                <flux:input wire:model="editUserContext.email" type="email" label="Correo electrónico" required />
            </div>

            <flux:switch 
                wire:model="editUserContext.is_active" 
                :disabled="$editingUser && $editingUser->id === auth()->id()"
                label="Estado {{ $editUserContext['is_active'] ? 'Activo' : 'Inactivo' }}" 
                description="{{ $editingUser && $editingUser->id === auth()->id() ? 'No puedes desactivar tu propia cuenta.' : 'Permite o bloquea el acceso al sistema.' }}" 
            />

            <flux:separator />

            <div class="grid grid-cols-2 gap-6">
                <div class="space-y-3">
                    <flux:heading size="sm">Perfiles</flux:heading>
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        @foreach($this->allProfiles as $profile)
                            <flux:checkbox wire:key="edituser-prof-{{ $profile->id }}" id="edituser-prof-{{ $profile->id }}" wire:model.live="selectedProfiles" value="{{ $profile->name }}" label="{{ str($profile->name)->headline() }}" />
                        @endforeach
                    </div>
                </div>

                <div class="space-y-3">
                    <flux:heading size="sm">Permisos (Directos y Heredados)</flux:heading>
                    <div class="space-y-6 max-h-96 overflow-y-auto pr-2 rounded-md border border-neutral-200 p-4 dark:border-neutral-700">
                        @foreach($this->permissions->groupBy('group_name') as $group => $groupPermissions)
                            <div class="space-y-3">
                                <flux:heading size="sm">{{ $group ?: 'Generales' }}</flux:heading>
                                <flux:separator variant="subtle" />
                                <div class="grid grid-cols-1 gap-3">
                                    @foreach($groupPermissions as $permission)
                                        @php
                                            $isInherited = in_array($permission->name, $this->inheritedPermissions);
                                        @endphp
                                        <flux:checkbox 
                                            wire:key="edituser-perm-{{ $permission->id }}"
                                            id="edituser-perm-{{ $permission->id }}"
                                            wire:model="selectedPermissions" 
                                            value="{{ $permission->name }}" 
                                            label="{{ $permission->display_name ?? str($permission->name)->headline() }}" 
                                            :disabled="$isInherited"
                                            :checked="$isInherited || in_array($permission->name, $this->selectedPermissions)"
                                        />
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="updateUser">Guardar Cambios</flux:button>
            </div>
        </div>
    </flux:modal>

    <x-modals.admin-roles :roles="$this->roles" />
    <x-modals.admin-permissions :permissions="$this->permissions" />
    <x-modals.admin-profiles :allProfiles="$this->allProfiles" />

</div>