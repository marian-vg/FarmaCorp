<?php

namespace Tests\Feature\Livewire\Admin;

use App\Livewire\Admin\ProfileManager;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Role::firstOrCreate(['name' => 'admin']);
        Permission::firstOrCreate(['name' => 'edit-users']);
        Permission::firstOrCreate(['name' => 'delete-users']);
    }

    public function test_admin_can_access_profile_manager()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.profiles'))
            ->assertSuccessful()
            ->assertSeeLivewire(ProfileManager::class);
    }

    public function test_non_admin_cannot_access_profile_manager()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.profiles'))
            ->assertForbidden();
    }

    public function test_component_renders_profiles()
    {
        $profile = Profile::create(['name' => 'Test Profile', 'description' => 'A test profile']);

        Livewire::test(ProfileManager::class)
            ->assertSee('Test Profile')
            ->assertSee('A test profile');
    }

    public function test_can_create_profile_with_permissions()
    {
        $permission = Permission::firstOrCreate(['name' => 'edit-users']);

        Livewire::test(ProfileManager::class)
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

        Livewire::test(ProfileManager::class)
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

        Livewire::test(ProfileManager::class)
            ->call('confirmDelete', $profile->id)
            ->call('deleteProfile')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('profiles', [
            'id' => $profile->id,
        ]);
    }
}
