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
}
