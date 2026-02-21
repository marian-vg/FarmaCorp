<?php

namespace Tests\Feature\Livewire\Admin;

use App\Models\User;
use App\Livewire\Admin\UserManagement;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

// Esto prepara el terreno para TODOS los tests del archivo
beforeEach(function () {
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'empleado']); // Lo necesitamos para el test de creación
});

it('permite al administrador ver la lista de usuarios', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin);

    Livewire::test(UserManagement::class)
        ->assertStatus(200)
        ->assertSee($admin->name);
});

it('crea un nuevo usuario correctamente', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin);

    Livewire::test(UserManagement::class)
        ->set('name', 'Juan Empleado')
        ->set('email', 'juan@farmacorp.com')
        ->set('password', 'password123')
        ->set('selectedRole', 'empleado')
        ->call('save');

    $this->assertDatabaseHas('users', ['email' => 'juan@farmacorp.com']);
});

it('no permite que un admin se elimine a sí mismo', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin);

    Livewire::test(UserManagement::class)
        ->call('delete', $admin->id);

    $this->assertDatabaseHas('users', ['id' => $admin->id, 'deleted_at' => null]);
});