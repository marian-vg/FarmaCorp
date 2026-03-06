<?php

namespace Tests\Feature\Livewire\Admin;

use App\Livewire\Admin\GroupManager;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GroupManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'empleado']);
    }

    public function test_admin_can_access_group_manager()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.groups'))
            ->assertSuccessful()
            ->assertSeeLivewire(GroupManager::class);
    }

    public function test_empleado_cannot_access_group_manager()
    {
        $empleado = User::factory()->create();
        $empleado->assignRole('empleado');

        $this->actingAs($empleado)
            ->get(route('admin.groups'))
            ->assertForbidden();
    }

    public function test_component_renders_groups()
    {
        Group::create(['name' => 'Analgésicos', 'description' => 'Dolor']);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Livewire::actingAs($admin)->test(GroupManager::class)
            ->assertSee('Analgésicos')
            ->assertSee('Dolor');
    }

    public function test_admin_can_search_groups()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Group::create(['name' => 'Antibióticos', 'description' => 'Infecciones']);
        Group::create(['name' => 'Vitamínicos', 'description' => 'Suplementos']);

        Livewire::actingAs($admin)->test(GroupManager::class)
            ->set('search', 'Anti')
            ->assertSee('Antibióticos')
            ->assertDontSee('Vitamínicos');
    }

    public function test_admin_can_create_group()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Livewire::actingAs($admin)->test(GroupManager::class)
            ->set('groupContext.name', 'Cardiología')
            ->set('groupContext.description', 'Medicamentos cardíacos')
            ->call('saveGroup')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('groups', ['name' => 'Cardiología']);
    }

    public function test_admin_can_edit_group()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $group = Group::create(['name' => 'OldName', 'description' => 'OldDesc']);

        Livewire::actingAs($admin)->test(GroupManager::class)
            ->call('editGroup', $group->id)
            ->set('groupContext.name', 'NewName')
            ->call('saveGroup')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('groups', ['name' => 'NewName']);
    }

    public function test_admin_can_deactivate_group()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $group = Group::create(['name' => 'ToDelete', 'description' => 'To Soft Delete']);

        Livewire::actingAs($admin)->test(GroupManager::class)
            ->call('confirmDeactivate', $group->id)
            ->call('deactivateGroup')
            ->assertHasNoErrors();

        $this->assertSoftDeleted('groups', ['id' => $group->id]);
    }
}
