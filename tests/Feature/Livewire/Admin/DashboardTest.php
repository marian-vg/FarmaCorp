<?php

namespace Tests\Feature\Livewire\Admin;

use App\Livewire\Admin\Dashboard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_successfully()
    {
        $admin = User::factory()->create();
        $adminRole = Role::findOrCreate('admin', 'web');
        $admin->assignRole($adminRole);

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->assertStatus(200);
    }

    public function test_admin_is_redirected_to_admin_dashboard()
    {
        $admin = User::factory()->create();
        $adminRole = Role::findOrCreate('admin', 'web');
        $admin->assignRole($adminRole);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_user_is_redirected_to_user_dashboard()
    {
        $user = User::factory()->create();
        // No admin role

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('user.dashboard'));
    }

    public function test_user_cannot_access_admin_dashboard()
    {
        $user = User::factory()->create();
        // No admin role

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertStatus(403);
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
}
