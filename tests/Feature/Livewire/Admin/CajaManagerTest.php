<?php

namespace Tests\Feature\Livewire\Admin;

use App\Models\Caja;
use App\Models\User;
use App\Livewire\Admin\CajaManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CajaManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_open_a_new_till(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CajaManager::class)
            ->set('monto_inicial', 1500.50)
            ->call('abrirCaja')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('cajas', [
            'user_id' => $user->id,
            'monto_inicial' => 1500.50,
        ]);
        
        $this->assertDatabaseCount('cajas', 1);
    }

    public function test_user_cannot_open_a_till_if_one_is_already_open(): void
    {
        $user = User::factory()->create();

        // Create an already open till for this user
        Caja::create([
            'fecha_apertura' => now(),
            'monto_inicial' => 1000,
            'user_id' => $user->id,
            // fecha_cierre is null by default
        ]);

        Livewire::actingAs($user)
            ->test(CajaManager::class)
            ->set('monto_inicial', 500)
            ->call('abrirCaja')
            ->assertHasErrors(['monto_inicial']);

        // Assert no new till was created
        $this->assertDatabaseCount('cajas', 1);
    }
}
