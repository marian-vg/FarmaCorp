<?php

namespace Tests\Feature\Livewire\Admin;

use App\Livewire\Admin\Dashboard;
use App\Models\Profile;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Register/Flush View Compiler caches for Flux to avoid test re-render resolution errors
        Blade::component('flux::card', 'flux::components.card');

        $this->seed(RoleAndPermissionSeeder::class);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());
    }

    public function test_renders_successfully()
    {
        $admin = User::factory()->create();
        $adminRole = Role::findOrCreate('admin', 'web');
        $admin->assignRole($adminRole);

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->assertStatus(200);
    }

    public function test_admin_can_search_users()
    {
        $admin = User::factory()->create();
        $adminRole = Role::findOrCreate('admin', 'web');
        $admin->assignRole($adminRole);

        $userToSearch = User::factory()->create(['name' => 'John Doe Search']);
        User::factory()->create(['name' => 'Jane Smith']);

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->set('search', 'John Doe Search')
            ->assertSee('John Doe Search')
            ->assertDontSee('Jane Smith');
    }

    public function test_admin_can_filter_by_is_active_status()
    {
        $admin = User::factory()->create();
        $adminRole = Role::findOrCreate('admin', 'web');
        $admin->assignRole($adminRole);

        $inactiveUser = User::factory()->create(['name' => 'Inactive Guy', 'is_active' => false]);
        $activeUser = User::factory()->create(['name' => 'Active Girl', 'is_active' => true]);

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->set('statusFilter', 'inactive')
            ->assertSee('Inactive Guy')
            ->assertDontSee('Active Girl');
    }

    public function test_admin_can_create_user()
    {
        $admin = User::factory()->create();
        $adminRole = Role::findOrCreate('admin', 'web');
        $admin->assignRole($adminRole);

        $role = Role::findOrCreate('employee', 'web');

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->set('newUserContext.name', 'New Guy')
            ->set('newUserContext.email', 'newguy@example.com')
            ->set('newUserContext.role', 'employee')
            ->set('newUserContext.password', 'password123')
            ->set('newUserContext.password_confirmation', 'password123')
            ->call('createUser')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'newguy@example.com']);
    }

    public function test_admin_can_deactivate_user()
    {
        $admin = User::factory()->create();
        $adminRole = Role::findOrCreate('admin', 'web');
        $admin->assignRole($adminRole);

        $userToDeactivate = User::factory()->create(['is_active' => true]);

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->call('deactivateUser', $userToDeactivate->id);

        $this->assertDatabaseHas('users', [
            'id' => $userToDeactivate->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_save_roles()
    {
        Event::fake();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
        Role::firstOrCreate(['name' => 'editor']);

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->call('editRoles', $user->id)
            ->set('selectedRoles', ['editor'])
            ->call('saveRoles')
            ->assertHasNoErrors();

        $this->assertTrue($user->fresh()->hasRole('editor'));
    }

    public function test_admin_can_save_permissions()
    {
        Event::fake();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
        Permission::firstOrCreate(['name' => 'publish-articles']);

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->call('editPermissions', $user->id)
            ->set('selectedPermissions', ['publish-articles'])
            ->call('savePermissions')
            ->assertHasNoErrors();

        $this->assertTrue($user->fresh()->hasDirectPermission('publish-articles'));
    }

    public function test_admin_can_save_profiles()
    {
        Event::fake();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $profile = Profile::create(['name' => 'Biller', 'description' => 'Billing profile']);

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->call('editProfiles', $user->id)
            ->set('selectedProfiles', [$profile->id])
            ->call('saveProfiles')
            ->assertHasNoErrors();

        $this->assertTrue($user->fresh()->profiles->contains($profile->id));
    }

    public function test_admin_can_edit_and_update_user()
    {
        Event::fake();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $targetUser = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@mail.com',
            'is_active' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->call('editUser', $targetUser->id)
            ->assertSet('editUserContext.name', 'Old Name')
            ->set('editUserContext.name', 'New Name')
            ->set('editUserContext.email', 'new@mail.com')
            ->set('editUserContext.is_active', false)
            ->call('updateUser')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'New Name',
            'email' => 'new@mail.com',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_save_alert_days()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->set('alertDays', 15)
            ->call('saveAlertDays')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('settings', [
            'key' => 'alert_days',
            'value' => '15',
        ]);
    }
}
