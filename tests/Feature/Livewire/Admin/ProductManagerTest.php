<?php

namespace Tests\Feature\Livewire\Admin;

use App\Livewire\Admin\ProductManager;
use App\Models\Group;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'empleado']);
    }

    public function test_admin_can_access_product_manager()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.products'))
            ->assertSuccessful();
    }

    public function test_empleado_cannot_access_product_manager()
    {
        $empleado = User::factory()->create();
        $empleado->assignRole('empleado');

        $this->actingAs($empleado)
            ->get(route('admin.products'))
            ->assertRedirect();
    }

    public function test_component_renders_products()
    {
        Product::factory()->create(['name' => 'Gasa', 'description' => 'Insumo Médico', 'status' => true]);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Livewire::actingAs($admin)->test(ProductManager::class)
            ->assertSee('Gasa')
            ->assertSee('N/D')
            ->assertSee('Insumo/General');
    }

    public function test_admin_can_search_products()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Product::create(['name' => 'Paracetamol', 'description' => 'Pastilla', 'status' => true]);
        Product::create(['name' => 'Diclofenaco', 'description' => 'Jarabe', 'status' => true]);

        Livewire::actingAs($admin)->test(ProductManager::class)
            ->set('search', 'Para')
            ->assertSee('Paracetamol')
            ->assertDontSee('Diclofenaco');
    }

    public function test_admin_can_create_standard_product()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Livewire::actingAs($admin)->test(ProductManager::class)
            ->set('productContext.name', 'Jeringa')
            ->set('productContext.description', '10ml')
            ->set('productContext.status', true)
            ->set('isMedicine', false)
            ->call('saveProduct')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('products', ['name' => 'Jeringa']);
    }

    public function test_admin_can_create_medicine()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $group = Group::create(['name' => 'Analgésicos']);

        Livewire::actingAs($admin)->test(ProductManager::class)
            ->set('productContext.name', 'Tafirol')
            ->set('productContext.description', '1g')
            ->set('productContext.status', true)
            ->set('isMedicine', true)
            ->set('medicineContext.price', 500.0)
            ->set('medicineContext.group_id', $group->id)
            ->set('medicineContext.level', 'Alta')
            ->set('medicineContext.leaflet', 'Tomar cada 8hs')
            ->set('medicineContext.expiration_date', '2027-01-01')
            ->set('medicineContext.is_psychotropic', false)
            ->call('saveProduct')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('products', ['name' => 'Tafirol']);
        $product = Product::where('name', 'Tafirol')->first();

        $this->assertDatabaseHas('medicines', [
            'product_id' => $product->id,
            'price' => 500.0,
            'group_id' => $group->id,
            'level' => 'Alta',
        ]);
    }

    public function test_admin_can_edit_product()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $product = Product::create(['name' => 'OldName', 'description' => 'OldDesc', 'status' => true]);

        Livewire::actingAs($admin)->test(ProductManager::class)
            ->call('editProduct', $product->id)
            ->set('productContext.name', 'NewName')
            ->call('saveProduct')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('products', ['name' => 'NewName']);
    }

    public function test_admin_can_deactivate_product()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $product = Product::create(['name' => 'ToDelete', 'description' => 'To Soft Delete', 'status' => true]);

        Livewire::actingAs($admin)->test(ProductManager::class)
            ->call('confirmDeactivate', $product->id)
            ->call('deactivateProduct')
            ->assertHasNoErrors();

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }
}
