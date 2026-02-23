<flux:modal name="add-user" class="min-w-[22rem]">
    <form wire:submit="createUser" class="space-y-6">
        <div>
            <flux:heading size="lg">Nuevo Usuario</flux:heading>
            <flux:subheading>Crear un nuevo usuario y asignar rol.</flux:subheading>
        </div>

        <flux:input wire:model="newUserContext.name" label="Nombre" placeholder="Andres Calamaro" required />
        <flux:input wire:model="newUserContext.email" type="email" label="Correo Electrónico" placeholder="andres@farmacorp.com" required />
        
        <flux:select wire:model="newUserContext.role" label="Perfil" placeholder="Selecciona un perfil" required>
            @foreach($this->roles as $role)
                <flux:select.option value="{{ $role->name }}">{{ str($role->name)->headline() }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:input wire:model="newUserContext.password" type="password" label="Contraseña" required />
        <flux:input wire:model="newUserContext.password_confirmation" type="password" label="Confirmar contraseña" required />
    
        <div class="flex justify-end gap-2">
            <flux:modal.close>
                <flux:button variant="ghost">Cancelar</flux:button>
            </flux:modal.close>
            <flux:button type="submit" variant="primary">Crear Usuario</flux:button>
        </div>
    </form>
</flux:modal>