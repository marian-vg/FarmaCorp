@props(['allProfiles'])

<flux:modal name="edit-profiles" class="min-w-xs">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Perfiles Activos</flux:heading>
            <flux:subheading>Asignar perfiles para otorgar sus permisos al usuario.</flux:subheading>
        </div>

        <div class="space-y-2 max-h-60 overflow-y-auto">
            @foreach($allProfiles as $profile)
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
