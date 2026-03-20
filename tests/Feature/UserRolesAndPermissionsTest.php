<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserRolesAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created_with_factory(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', [
            'email' => $user->email,
            'is_active' => true,
        ]);
    }

    public function test_user_can_be_created_as_inactive(): void
    {
        $user = User::factory()->inactive()->create();

        $this->assertFalse($user->is_active);
        $this->assertDatabaseHas('users', [
            'email' => $user->email,
            'is_active' => false,
        ]);
    }

    public function test_user_can_have_a_role_assigned(): void
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'editor']);

        $user->assignRole($role);

        $this->assertTrue($user->hasRole('editor'));
    }

    public function test_user_can_have_multiple_roles(): void
    {
        $user = User::factory()->create();
        Role::firstOrCreate(['name' => 'editor']);
        Role::firstOrCreate(['name' => 'moderator']);

        $user->assignRole('editor', 'moderator');

        $this->assertTrue($user->hasRole('editor'));
        $this->assertTrue($user->hasRole('moderator'));
        $this->assertCount(2, $user->roles);
    }

    public function test_user_can_have_a_permission_assigned_directly(): void
    {
        $user = User::factory()->create();
        Permission::create(['name' => 'create-user']);

        $user->givePermissionTo('create-user');

        $this->assertTrue($user->hasPermissionTo('create-user'));
    }

    public function test_user_can_have_multiple_permissions(): void
    {
        $user = User::factory()->create();
        Permission::create(['name' => 'create-user']);
        Permission::create(['name' => 'modify-user']);

        $user->givePermissionTo('create-user', 'modify-user');

        $this->assertTrue($user->hasPermissionTo('create-user'));
        $this->assertTrue($user->hasPermissionTo('modify-user'));
    }

    public function test_role_can_have_permissions(): void
    {
        $role = Role::firstOrCreate(['name' => 'admin']);
        Permission::create(['name' => 'create-user']);
        Permission::create(['name' => 'modify-user']);

        $role->givePermissionTo('create-user', 'modify-user');

        $this->assertTrue($role->hasPermissionTo('create-user'));
        $this->assertTrue($role->hasPermissionTo('modify-user'));
    }

    public function test_user_inherits_permissions_from_role(): void
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'admin']);
        Permission::create(['name' => 'create-user']);
        Permission::create(['name' => 'modify-user']);

        $role->givePermissionTo('create-user', 'modify-user');
        $user->assignRole($role);

        $this->assertTrue($user->hasPermissionTo('create-user'));
        $this->assertTrue($user->hasPermissionTo('modify-user'));
    }

    public function test_admin_user_has_admin_role_and_permissions(): void
    {
        $user = User::factory()->create();

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        Permission::create(['name' => 'create-user']);
        Permission::create(['name' => 'modify-user']);

        $adminRole->givePermissionTo('create-user', 'modify-user');
        $user->assignRole($adminRole);

        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasPermissionTo('create-user'));
        $this->assertTrue($user->hasPermissionTo('modify-user'));
    }

    public function test_basic_user_does_not_have_admin_permissions(): void
    {
        $basicUser = User::factory()->create();
        Role::firstOrCreate(['name' => 'basic']);
        Permission::create(['name' => 'create-user']);
        Permission::create(['name' => 'modify-user']);

        $basicUser->assignRole('basic');

        $this->assertTrue($basicUser->hasRole('basic'));
        $this->assertFalse($basicUser->hasRole('admin'));
        $this->assertFalse($basicUser->hasPermissionTo('create-user'));
        $this->assertFalse($basicUser->hasPermissionTo('modify-user'));
    }

    public function test_user_role_can_be_removed(): void
    {
        $user = User::factory()->create();
        Role::firstOrCreate(['name' => 'admin']);

        $user->assignRole('admin');
        $this->assertTrue($user->hasRole('admin'));

        $user->removeRole('admin');
        $this->assertFalse($user->hasRole('admin'));
    }

    public function test_user_permission_can_be_revoked(): void
    {
        $user = User::factory()->create();
        Permission::create(['name' => 'create-user']);

        $user->givePermissionTo('create-user');
        $this->assertTrue($user->hasPermissionTo('create-user'));

        $user->revokePermissionTo('create-user');
        $this->assertFalse($user->hasPermissionTo('create-user'));
    }

    public function test_user_roles_can_be_synced(): void
    {
        $user = User::factory()->create();
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'basic']);

        $user->assignRole('admin');
        $this->assertTrue($user->hasRole('admin'));

        $user->syncRoles(['basic']);
        $this->assertFalse($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('basic'));
    }
}
