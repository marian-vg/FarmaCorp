<?php

namespace Tests\Feature\Livewire\Admin;

use App\Livewire\Admin\ClientManager;
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
    }

    public function test_admin_can_access_client_manager()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.clients'))
            ->assertSuccessful()
            ->assertSeeLivewire(ClientManager::class);
    }

    public function test_non_admin_cannot_access_client_manager()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.clients'))
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

        Livewire::test(ClientManager::class)
            ->assertSee('John Doe')
            ->assertSee('john@example.com')
            ->assertSee('123456789');
    }

    public function test_can_search_clients_by_name_or_phone()
    {
        Client::create([
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'phone' => '111',
            'address' => 'St 1',
        ]);

        Client::create([
            'first_name' => 'Bob',
            'last_name' => 'Jones',
            'phone' => '222',
            'address' => 'St 2',
        ]);

        Livewire::test(ClientManager::class)
            ->set('search', 'Alice')
            ->assertSee('Alice Smith')
            ->assertDontSee('Bob Jones')
            ->set('search', '222')
            ->assertSee('Bob Jones')
            ->assertDontSee('Alice Smith');
    }

    public function test_can_filter_active_and_inactive_clients()
    {
        Client::create([
            'first_name' => 'Active',
            'last_name' => 'Client',
            'phone' => '1',
            'address' => '1',
            'is_active' => true,
        ]);

        Client::create([
            'first_name' => 'Inactive',
            'last_name' => 'Client',
            'phone' => '2',
            'address' => '2',
            'is_active' => false,
        ]);

        Livewire::test(ClientManager::class)
            ->set('statusFilter', 'active')
            ->assertSee('Active Client')
            ->assertDontSee('Inactive Client')
            ->set('statusFilter', 'inactive')
            ->assertSee('Inactive Client')
            ->assertDontSee('Active Client')
            ->set('statusFilter', 'all')
            ->assertSee('Active Client')
            ->assertSee('Inactive Client');
    }

    public function test_can_create_client()
    {
        Livewire::test(ClientManager::class)
            ->set('clientContext.first_name', 'New')
            ->set('clientContext.last_name', 'Client')
            ->set('clientContext.email', 'new@example.com')
            ->set('clientContext.phone', '5551234')
            ->set('clientContext.address', 'New St')
            ->call('saveClient')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('clients', [
            'first_name' => 'New',
            'email' => 'new@example.com',
        ]);
    }

    public function test_can_edit_client()
    {
        $client = Client::create([
            'first_name' => 'Old',
            'last_name' => 'Client',
            'phone' => '123',
            'address' => 'Old St',
        ]);

        Livewire::test(ClientManager::class)
            ->call('editClient', $client->id)
            ->set('clientContext.first_name', 'Updated')
            ->call('saveClient')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'first_name' => 'Updated',
            'last_name' => 'Client',
        ]);
    }

    public function test_can_deactivate_and_reactivate_client()
    {
        $client = Client::create([
            'first_name' => 'Logical',
            'last_name' => 'Delete',
            'phone' => '123',
            'address' => 'St',
            'is_active' => true,
        ]);

        $component = Livewire::test(ClientManager::class)
            ->call('confirmDeactivate', $client->id)
            ->call('deactivateClient')
            ->assertHasNoErrors();

        $this->assertFalse($client->fresh()->is_active);

        $component->call('reactivateClient', $client->id)
            ->assertHasNoErrors();

        $this->assertTrue($client->fresh()->is_active);
    }
}
