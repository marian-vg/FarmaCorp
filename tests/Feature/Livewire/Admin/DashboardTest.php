<?php

namespace Tests\Feature\Livewire\Admin;

use App\Models\User;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;


class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_render(): void
    {
        $user = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        $adminRole = Role::create(['name' => 'admin']);

        $user->assignRole($adminRole);

        $this->actingAs($user);

        $component = Volt::test('admin.dashboard');

        $component->assertSee('Admin Dashboard');
    }

}
