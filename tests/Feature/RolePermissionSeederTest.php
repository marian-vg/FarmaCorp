<?php

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Definimos una ruta de prueba estrictamente protegida por permiso
    Route::get('/test-caja', function () {
        return 'Caja Abierta';
    })->middleware('permission:caja.abrir');
});

it('seeds the generic format correctly with group names', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $cajaAbrirPermiso = Permission::where('name', 'caja.abrir')->first();

    expect($cajaAbrirPermiso)->not->toBeNull()
        ->and($cajaAbrirPermiso->group_name)->toBe('Caja');

    $usuariosCrearEditar = Permission::where('name', 'usuarios.crear_editar')->first();

    expect($usuariosCrearEditar)->not->toBeNull()
        ->and($usuariosCrearEditar->group_name)->toBe('Usuarios');

    $superAdminRole = Role::where('name', 'super-admin')->first();

    expect($superAdminRole)->not->toBeNull()
        ->and($superAdminRole->hasPermissionTo('caja.abrir'))->toBeTrue();
});

it('restricts access for users without caja.abrir permission', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/test-caja');

    // Dado que capturamos AuthorizationException en app.php y lanzamos redirect a fallback
    // Esperamos un error redireccionado con session('notify') o un estado 302 hacia previous.
    // Como en tests no hay previous URL explícita a veces, veremos un Status Código de redirect o 403.
    // Usaremos expect()->toBeRedirect() ya que programamos el flash en bootstrap/app.php
    $response->assertRedirect();
    $response->assertSessionHas('notify');
    expect(session('notify')['type'])->toBe('error');
});

it('allows access for a super-admin with caja.abrir permission', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->get('/test-caja');

    $response->assertOk();
    $response->assertSee('Caja Abierta');
});
