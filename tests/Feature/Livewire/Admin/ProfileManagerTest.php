<?php

namespace Tests\Feature\Livewire\Admin;

use App\Livewire\Admin\ProfileManager;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileManagerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        Permission::firstOrCreate(['name' => 'edit-users']);
        Permission::firstOrCreate(['name' => 'delete-users']);
    }

    public function test_admin_can_access_profile_manager()
    {
        $this->actingAs($this->admin)
            ->get(route('admin.profiles'))
            ->assertSuccessful();
    }

    public function test_non_admin_cannot_access_profile_manager()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.profiles'))
            ->assertRedirect();
    }

    public function test_component_renders_profiles()
    {
        $profile = Profile::create(['name' => 'Test Profile', 'description' => 'A test profile']);

        Livewire::actingAs($this->admin)->test(ProfileManager::class)
            ->assertSee('Test Profile')
            ->assertSee('A test profile');
    }

    public function test_can_create_profile_with_permissions()
    {
        $permission = Permission::firstOrCreate(['name' => 'edit-users']);

        Livewire::actingAs($this->admin)->test(ProfileManager::class)
            ->set('profileContext.name', 'New Test Profile')
            ->set('profileContext.description', 'New Test Description')
            ->set('selectedPermissions', [$permission->name])
            ->call('saveProfile')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('profiles', [
            'name' => 'New Test Profile',
            'description' => 'New Test Description',
        ]);

        $profile = Profile::where('name', 'New Test Profile')->first();
        $this->assertTrue($profile->hasPermissionTo('edit-users'));
    }

    public function test_can_edit_profile()
    {
        $profile = Profile::create(['name' => 'Old Name', 'description' => 'Old Desc']);
        $permission = Permission::firstOrCreate(['name' => 'delete-users']);

        Livewire::actingAs($this->admin)->test(ProfileManager::class)
            ->call('editProfile', $profile->id)
            ->set('profileContext.name', 'Updated Name')
            ->set('selectedPermissions', [$permission->name])
            ->call('saveProfile')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('profiles', [
            'id' => $profile->id,
            'name' => 'Updated Name',
        ]);

        $this->assertTrue($profile->fresh()->hasPermissionTo('delete-users'));
    }

    public function test_can_delete_profile()
    {
        $profile = Profile::create(['name' => 'To Be Deleted']);

        Livewire::actingAs($this->admin)->test(ProfileManager::class)
            ->call('confirmDelete', $profile->id)
            ->call('deleteProfile')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('profiles', [
            'id' => $profile->id,
        ]);
    }

    public function test_can_create_permission()
    {
        Livewire::actingAs($this->admin)->test(ProfileManager::class)
            ->set('permissionContext.display_name', 'Test Permission')
            ->set('permissionContext.description', 'A test permission')
            ->call('savePermission')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('permissions', [
            'name' => 'test-permission', // Slugified
            'display_name' => 'Test Permission',
            'description' => 'A test permission',
        ]);
    }

    public function test_can_edit_permission()
    {
        $permission = Permission::create([
            'name' => 'old-permission',
            'display_name' => 'Old Permission',
        ]);

        Livewire::actingAs($this->admin)->test(ProfileManager::class)
            ->call('editPermission', $permission->id)
            ->set('permissionContext.display_name', 'Updated Permission')
            ->set('permissionContext.description', 'Updated description')
            ->call('savePermission')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('permissions', [
            'id' => $permission->id,
            'name' => 'old-permission', // Immutable slug
            'display_name' => 'Updated Permission',
            'description' => 'Updated description',
        ]);
    }

    public function test_can_delete_permission()
    {
        $permission = Permission::create(['name' => 'delete_permission']);

        Livewire::actingAs($this->admin)->test(ProfileManager::class)
            ->call('confirmDeletePermission', $permission->id)
            ->call('deletePermission')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('permissions', [
            'id' => $permission->id,
        ]);
    }

    public function test_permission_update_clears_cache_and_keeps_slug_intact()
    {
        $user = User::factory()->create();

        $permission = Permission::create([
            'name' => 'crear-usuario',
            'display_name' => 'Crear Usuario',
            'description' => 'Permite crear',
        ]);

        $user->givePermissionTo($permission);

        $this->assertTrue($user->hasPermissionTo('crear-usuario'));

        Livewire::actingAs($this->admin)
            ->test(ProfileManager::class)
            ->call('editPermission', $permission->id)
            ->set('permissionContext.display_name', 'Crear Nuevo Usuario')
            ->set('permissionContext.description', 'Nueva descripcion')
            ->call('savePermission')
            ->assertHasNoErrors();

        $permission->refresh();
        $this->assertEquals('crear-usuario', $permission->name); // slug unchanged
        $this->assertEquals('Crear Nuevo Usuario', $permission->display_name);

        // El cache ha sido invalidado automáticamente en Livewire. Resfresh del usuario para comprobar.
        $user->refresh();
        $this->assertTrue($user->hasPermissionTo('crear-usuario'));

        // Assert we don't have a newly named permission giving access falsely
        // Spatie throws an exception if the permission doesn't exist in the DB at all
        try {
            $user->hasPermissionTo('crear-nuevo-usuario');
            $this->fail('Expected PermissionDoesNotExist exception was not thrown.');
        } catch (PermissionDoesNotExist $e) {
            $this->assertTrue(true);
        }
    }
}
