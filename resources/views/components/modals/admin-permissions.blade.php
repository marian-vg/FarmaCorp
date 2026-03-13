@props(['permissions'])

<flux:modal name="edit-permissions" class="min-w-xs">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Permisos Directos</flux:heading>
            <flux:subheading>Asignar permisos excepcionales directos.</flux:subheading>
        </div>

        <div class="space-y-2 max-h-60 overflow-y-auto">
            @if($permissions->isEmpty())
                {{-- Mensaje de alerta cuando no hay permisos disponibles --}}
                <div class="p-4 border border-dashed border-gray-300 dark:border-zinc-700 rounded-lg text-center bg-gray-50 dark:bg-zinc-800">
                    <flux:text class="text-sm text-gray-500">
                        No hay permisos operativos disponibles para asignar a este usuario en este momento.
                    </flux:text>
                </div>
            @else
                <div class="space-y-6 max-h-96 overflow-y-auto pr-2 rounded-md border border-neutral-200 p-4 dark:border-neutral-700">
                    @foreach($permissions->groupBy('group_name') as $group => $groupPermissions)
                        <div class="space-y-3">
                            <flux:heading size="sm">{{ $group ?: 'Generales' }}</flux:heading>
                            <flux:separator variant="subtle" />
                            <div class="grid grid-cols-1 gap-3">
                                @foreach($groupPermissions as $permission)
                                    @php
                                        $isInherited = in_array($permission->name, $this->inheritedPermissions);
                                    @endphp
                                    <flux:checkbox 
                                            wire:key="admin-perm-{{ $permission->id }}"
                                            id="admin-perm-{{ $permission->id }}"
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
            @endif
        </div>

        <div class="flex justify-end gap-2">
            <flux:modal.close>
                <flux:button variant="ghost">Cancelar</flux:button>
            </flux:modal.close>
            
            {{-- El botón se deshabilita si la lista de permisos está vacía --}}
            <flux:button 
                variant="primary" 
                wire:click="savePermissions"
                :disabled="$permissions->isEmpty()"
            >
                Guardar Cambios
            </flux:button>
        </div>
    </div>
</flux:modal>
