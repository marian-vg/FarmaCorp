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
        $admin = User::factory()->create(['is_active' => true]);
        $cajero = User::factory()->create(['is_active' => true]);

        Livewire::actingAs($admin)
            ->test(CajaManager::class)
            ->set('user_id', $cajero->id)
            ->set('monto_inicial', 1500.50)
            ->call('abrirCaja')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('cajas', [
            'user_id' => $cajero->id,
            'monto_inicial' => 1500.50,
        ]);
        
        $this->assertDatabaseCount('cajas', 1);
    }

    public function test_user_cannot_open_a_till_if_one_is_already_open(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $cajero = User::factory()->create(['is_active' => true]);

        // Create an already open till for this user
        Caja::create([
            'fecha_apertura' => now(),
            'monto_inicial' => 1000,
            'user_id' => $cajero->id,
            // fecha_cierre is null by default
        ]);

        Livewire::actingAs($admin)
            ->test(CajaManager::class)
            ->set('user_id', $cajero->id)
            ->set('monto_inicial', 500)
            ->call('abrirCaja')
            ->assertHasErrors(['caja_status']);

        // Assert no new till was created
        $this->assertDatabaseCount('cajas', 1);
    }

    public function test_can_search_tills_by_user_name(): void
    {
        $admin = User::factory()->create();
        $user1 = User::factory()->create(['name' => 'John Doe']);
        $user2 = User::factory()->create(['name' => 'Jane Smith']);

        Caja::create(['fecha_apertura' => now(), 'monto_inicial' => 100, 'user_id' => $user1->id]);
        Caja::create(['fecha_apertura' => now(), 'monto_inicial' => 200, 'user_id' => $user2->id]);

        Livewire::actingAs($admin)
            ->test(CajaManager::class)
            ->set('search', 'John')
            ->assertCount('cajas', 1);
    }

    public function test_can_filter_tills_by_user_id(): void
    {
        $admin = User::factory()->create();
        $user1 = User::factory()->create(['name' => 'Alice']);
        $user2 = User::factory()->create(['name' => 'Bob']);

        Caja::create(['fecha_apertura' => now(), 'monto_inicial' => 100, 'user_id' => $user1->id]);
        Caja::create(['fecha_apertura' => now(), 'monto_inicial' => 200, 'user_id' => $user2->id]);

        Livewire::actingAs($admin)
            ->test(CajaManager::class)
            ->set('filtro_usuario', $user2->id)
            ->assertCount('cajas', 1);
    }

    public function test_clear_filters_resets_properties(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)
            ->test(CajaManager::class)
            ->set('search', 'Test')
            ->set('filtro_usuario', 1)
            ->set('fecha_desde', '2026-01-01')
            ->set('fecha_hasta', '2026-12-31')
            ->call('limpiarFiltros')
            ->assertSet('search', '')
            ->assertSet('filtro_usuario', '')
            ->assertSet('fecha_desde', '')
            ->assertSet('fecha_hasta', '');
    }
}
