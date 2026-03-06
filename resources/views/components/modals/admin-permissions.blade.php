@props(['permissions'])

<flux:modal name="edit-permissions" class="min-w-xs">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Permisos Directos</flux:heading>
            <flux:subheading>Asignar permisos excepcionales directos.</flux:subheading>
        </div>

        <div class="space-y-2 max-h-60 overflow-y-auto">
            {{-- Usamos @forelse para detectar si la colección está vacía --}}
            @forelse($permissions as $permission)
                <flux:checkbox 
                    wire:model="selectedPermissions" 
                    value="{{ $permission->name }}" 
                    label="{{ str($permission->display_name ?? $permission->name)->headline() }}" 
                />
            @empty
                {{-- Mensaje de alerta cuando no hay permisos disponibles --}}
                <div class="p-4 border border-dashed border-gray-300 dark:border-zinc-700 rounded-lg text-center bg-gray-50 dark:bg-zinc-800">
                    <flux:text class="text-sm text-gray-500">
                        No hay permisos operativos disponibles para asignar a este usuario en este momento.
                    </flux:text>
                </div>
            @endforelse
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
