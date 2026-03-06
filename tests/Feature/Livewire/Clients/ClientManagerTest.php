<?php

namespace Tests\Feature\Livewire\Clients;

use App\Livewire\Clients\ClientManager;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClientManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'empleado']);
    }

    public function test_admin_can_access_client_manager()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('clients.index'))
            ->assertSuccessful()
            ->assertSeeLivewire(ClientManager::class);
    }

    public function test_empleado_can_access_client_manager()
    {
        $empleado = User::factory()->create();
        $empleado->assignRole('empleado');

        $this->actingAs($empleado)
            ->get(route('clients.index'))
            ->assertSuccessful()
            ->assertSeeLivewire(ClientManager::class);
    }

    public function test_non_authorized_cannot_access_client_manager()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('clients.index'))
            ->assertForbidden();
    }

    public function test_component_renders_clients()
    {
        $client = Client::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '123456789',
            'address' => '123 Main St',
        ]);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Livewire::actingAs($admin)->test(ClientManager::class)
            ->assertSee('John Doe');
    }

    public function test_empleado_can_save_client()
    {
        $empleado = User::factory()->create();
        $empleado->assignRole('empleado');

        Livewire::actingAs($empleado)
            ->test(ClientManager::class)
            ->set('clientContext.first_name', 'Empleado')
            ->set('clientContext.last_name', 'Client')
            ->set('clientContext.phone', '5551234')
            ->set('clientContext.address', 'New St')
            ->call('saveClient')
            ->assertHasNoErrors();
    }

    public function test_empleado_can_edit_client()
    {
        $client = Client::create([
            'first_name' => 'Old',
            'last_name' => 'Client',
            'phone' => '123',
            'address' => 'Old St',
        ]);

        $empleado = User::factory()->create();
        $empleado->assignRole('empleado');

        Livewire::actingAs($empleado)
            ->test(ClientManager::class)
            ->call('editClient', $client->id)
            ->assertHasNoErrors();
    }

    public function test_empleado_can_deactivate_client()
    {
        $client = Client::create([
            'first_name' => 'Logical',
            'last_name' => 'Delete',
            'phone' => '123',
            'address' => 'St',
            'is_active' => true,
        ]);

        $empleado = User::factory()->create();
        $empleado->assignRole('empleado');

        Livewire::actingAs($empleado)
            ->test(ClientManager::class)
            ->call('confirmDeactivate', $client->id)
            ->assertHasNoErrors();

        Livewire::actingAs($empleado)
            ->test(ClientManager::class)
            ->set('editingClient', $client)
            ->call('deactivateClient')
            ->assertHasNoErrors();
    }

    public function test_admin_can_save_edit_and_deactivate()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $client = Client::create([
            'first_name' => 'Active',
            'last_name' => 'Client',
            'phone' => '1',
            'address' => '1',
            'is_active' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(ClientManager::class)
            ->set('clientContext.first_name', 'New')
            ->set('clientContext.last_name', 'Client')
            ->set('clientContext.phone', '5551234')
            ->set('clientContext.address', 'New St')
            ->call('saveClient')
            ->assertHasNoErrors();

        Livewire::actingAs($admin)
            ->test(ClientManager::class)
            ->call('editClient', $client->id)
            ->assertHasNoErrors();

        Livewire::actingAs($admin)
            ->test(ClientManager::class)
            ->call('confirmDeactivate', $client->id)
            ->assertHasNoErrors();
    }
}
