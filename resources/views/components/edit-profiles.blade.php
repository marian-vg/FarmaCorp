@props(['user'])

<flux:modal name="edit-profile-{{ $user->id }}" flyout variant="floating" class="w-64">
    <div class="space-y-6">
        <flux:heading size="lg">Update Role</flux:heading>
        <flux:subheading>Make changes to the user role.</flux:subheading>

        <flux:select class="w-40" aria-label="Perfiles del usuario" size="sm">
            @forelse ($user->getRoleNames() as $role)
                <flux:select.option size="sm" value="{{ $role }}">
                    {{ str($role)->headline() }}
                </flux:select.option>
            @empty
                <flux:select.option size="sm" value="" disabled selected>
                    none
                </flux:select.option>
            @endforelse
        </flux:select>
    </div>
</flux:modal>