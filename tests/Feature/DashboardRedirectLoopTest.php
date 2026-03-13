<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('does not cause a redirect loop for users with caja.acceder permission but no role', function () {
    // Seeder should already run from TestCase setting up the basic permissions
    // But we'll ensure our user gets the explicit permission only.
    Permission::firstOrCreate(['name' => 'caja.acceder']);
    Permission::firstOrCreate(['name' => 'roles.acceder']);
    Role::firstOrCreate(['name' => 'admin']);

    $user = User::factory()->create();
    $user->givePermissionTo('caja.acceder');

    // The user MUST NOT have the 'empleado' role explicitly attached here,
    // to test the Phase F bugfix where the role middleware caused the loop.

    // 1. Validar que la redirección original del portal de acceso funciona si tratare de ir a /dashboard
    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('user.dashboard'));

    // 2. Validar que la ruta de destino EFECTIVAMENTE carga y no rebota devuelta a un 302
    $this->actingAs($user)
        ->get(route('user.dashboard'))
        ->assertStatus(200);
});
