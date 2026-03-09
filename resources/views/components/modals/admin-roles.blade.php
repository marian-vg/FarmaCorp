@props(['roles'])

<flux:modal name="edit-roles" class="min-w-xs">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Roles del Usuario</flux:heading>
            <flux:subheading>Asignar roles base al usuario actual.</flux:subheading>
        </div>

        <div class="space-y-2 max-h-60 overflow-y-auto">
            @foreach($roles as $role)
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
