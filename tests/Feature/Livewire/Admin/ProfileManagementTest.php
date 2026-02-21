<?php

namespace Tests\Feature\Livewire\Admin;

use App\Models\User;
use App\Models\Profile;
use App\Livewire\Admin\ProfileManagement;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::create(['name' => 'admin']);
});

it('registra quién creó el perfil automáticamente', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin);

    Livewire::test(ProfileManagement::class)
        ->set('name', 'Perfil de Caja')
        ->call('save');

    $this->assertDatabaseHas('profiles', [
        'name' => 'Perfil de Caja',
        'created_by' => $admin->id
    ]);
});

it('puede enviar un perfil a la papelera y restaurarlo', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $profile = Profile::create([
        'name' => 'Perfil Temporal', 
        'created_by' => $admin->id
    ]);

    $this->actingAs($admin);

    // Testar eliminación
    Livewire::test(ProfileManagement::class)
        ->call('delete', $profile->id);

    $this->assertSoftDeleted('profiles', ['id' => $profile->id]);

    // Testar restauración
    Livewire::test(ProfileManagement::class)
        ->call('restore', $profile->id);

    $this->assertDatabaseHas('profiles', [
        'id' => $profile->id, 
        'deleted_at' => null
    ]);
});