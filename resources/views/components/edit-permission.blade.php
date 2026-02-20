@props(['user'])

<flux:modal name="edit-permission-{{ $user->id }}" flyout variant="floating" class="w-64">
    <div class="space-y-6">
        <flux:heading size="lg">Update Permission</flux:heading>
        <flux:text>Make changes to the user permissions.</flux:text>

        <flux:select class="w-40" aria-label="Permisos del usuario" size="sm">
            @forelse ($user->getPermissionsViaRoles() as $permission)
                <flux:select.option size="sm" value="{{ $permission->name }}">
                    {{ str($permission->name)->headline() }}
                </flux:select.option>
            @empty
                <flux:select.option size="sm" value="" disabled selected>
                    none
                </flux:select.option>
            @endforelse
        </flux:select>
    </div>
</flux:modal>