<?php

declare(strict_types=1);

use App\Livewire\Admin\Dashboard;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

it('cancels action and notifies user when lacking permissions (Desactivar Usuario)', function () {
    // 1. Arrange: Usuario sin el permiso 'usuarios.desactivar'
    $unauthorizedUser = User::factory()->create();
    // No le asignamos roles/permisos

    // Usuario objetivo para la acción
    $targetUser = User::factory()->create(['is_active' => true]);

    // 2. Act: Simulamos el componente e intentamos desactivar
    Livewire::actingAs($unauthorizedUser)
        ->test(Dashboard::class)
        ->call('deactivateUser', $targetUser->id)

        // 3. Assert: Comprobamos el dispatch de la notificación de error
        ->assertDispatched('notify', message: 'No tienes permisos para realizar esta acción: Desactivar usuario', type: 'error');

    // Comprobamos que el usuario objetivo NO cambió su estado
    expect($targetUser->fresh()->is_active)->toBeTrue();
});

it('cancels action and notifies user when lacking permissions (Modificar Configuración)', function () {
    $unauthorizedUser = User::factory()->create();

    Livewire::actingAs($unauthorizedUser)
        ->test(Dashboard::class)
        ->set('alertDays', 15)
        ->call('saveAlertDays')
        ->assertDispatched('notify', message: 'No tienes permisos para realizar esta acción: Modificar configuración', type: 'error');
});

it('cancels action and notifies user when lacking permissions (Crear Usuario)', function () {
    $unauthorizedUser = User::factory()->create();

    Livewire::actingAs($unauthorizedUser)
        ->test(Dashboard::class)
        ->set('newUserContext.name', 'Hacker')
        ->call('createUser')
        ->assertDispatched('notify', message: 'No tienes permisos para realizar esta acción: Crear usuario', type: 'error');
});

it('cancels action and notifies user when lacking permissions (Editar Usuario)', function () {
    $unauthorizedUser = User::factory()->create();
    $targetUser = User::factory()->create();

    Livewire::actingAs($unauthorizedUser)
        ->test(Dashboard::class)
        ->call('editUser', $targetUser->id)
        ->assertDispatched('notify', message: 'No tienes permisos para realizar esta acción: Editar usuario', type: 'error');
});

it('cancels action and notifies user when lacking permissions (Modificar Roles)', function () {
    $unauthorizedUser = User::factory()->create();
    $targetUser = User::factory()->create();

    Livewire::actingAs($unauthorizedUser)
        ->test(Dashboard::class)
        ->call('editRoles', $targetUser->id)
        ->assertDispatched('notify', message: 'No tienes permisos para realizar esta acción: Modificar roles de usuario', type: 'error');
});
