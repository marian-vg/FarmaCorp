<?php

use App\Events\UserPermissionsUpdated;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\ProfileManager;
use App\Models\Profile;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');

    $this->targetUser = User::factory()->create();
});

it('dispatches UserPermissionsUpdated when saving roles in Dashboard', function () {
    Event::fake();

    Role::create(['name' => 'editor']);

    Livewire::actingAs($this->admin)
        ->test(Dashboard::class)
        ->set('editingUser', $this->targetUser)
        ->set('selectedRoles', ['editor'])
        ->call('saveRoles');

    Event::assertDispatched(UserPermissionsUpdated::class, function ($event) {
        return $event->user->id === $this->targetUser->id;
    });
});

it('dispatches UserPermissionsUpdated when saving permissions in Dashboard', function () {
    Event::fake();

    Permission::create(['name' => 'extra-permission']);
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    Livewire::actingAs($this->admin)
        ->test(Dashboard::class)
        ->set('editingUser', $this->targetUser)
        ->set('selectedPermissions', ['extra-permission'])
        ->call('savePermissions');

    Event::assertDispatched(UserPermissionsUpdated::class, function ($event) {
        return $event->user->id === $this->targetUser->id;
    });
});

it('dispatches UserPermissionsUpdated when updating a user in Dashboard', function () {
    Event::fake();

    // Create permission and clear cache to avoid Spatie errors
    Permission::create(['name' => 'another-permission']);
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    Livewire::actingAs($this->admin)
        ->test(Dashboard::class)
        ->set('editingUser', $this->targetUser)
        ->set('selectedPermissions', ['another-permission'])
        ->set('editUserContext', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'is_active' => true,
        ])
        ->call('updateUser');

    Event::assertDispatched(UserPermissionsUpdated::class, function ($event) {
        return $event->user->id === $this->targetUser->id;
    });
});

it('dispatches UserPermissionsUpdated for all users in profile when profile permissions are saved', function () {
    Event::fake();

    $profile = Profile::create(['name' => 'Sales Profile']);
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $profile->users()->attach([$user1->id, $user2->id]);

    Permission::create(['name' => 'sales-permission']);
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    Livewire::actingAs($this->admin)
        ->test(ProfileManager::class)
        ->set('editingProfile', $profile)
        ->set('profileContext.name', $profile->name)
        ->set('selectedPermissions', ['sales-permission'])
        ->call('saveProfile');

    Event::assertDispatched(UserPermissionsUpdated::class, function ($event) use ($user1) {
        return $event->user->id === $user1->id;
    });

    Event::assertDispatched(UserPermissionsUpdated::class, function ($event) use ($user2) {
        return $event->user->id === $user2->id;
    });
});
