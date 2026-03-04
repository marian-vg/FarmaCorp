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
            ->assertSuccessful()
            ->assertSeeLivewire(ProductManager::class);
    }

    public function test_empleado_cannot_access_product_manager()
    {
        $empleado = User::factory()->create();
        $empleado->assignRole('empleado');

        $this->actingAs($empleado)
            ->get(route('admin.products'))
            ->assertForbidden();
    }

    public function test_component_renders_products()
    {
        Product::factory()->create(['name' => 'Gasa', 'description' => 'Insumo Médico', 'price' => 10.50, 'status' => true]);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Livewire::actingAs($admin)->test(ProductManager::class)
            ->assertSee('Gasa')
            ->assertSee('10.50')
            ->assertSee('Insumo/General');
    }

    public function test_admin_can_search_products()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Product::create(['name' => 'Paracetamol', 'description' => 'Pastilla', 'price' => 5.0, 'status' => true]);
        Product::create(['name' => 'Ibuprofeno', 'description' => 'Jarabe', 'price' => 15.0, 'status' => true]);

        Livewire::actingAs($admin)->test(ProductManager::class)
            ->set('search', 'Para')
            ->assertSee('Paracetamol')
            ->assertDontSee('Ibuprofeno');
    }

    public function test_admin_can_create_standard_product()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Livewire::actingAs($admin)->test(ProductManager::class)
            ->set('productContext.name', 'Jeringa')
            ->set('productContext.description', '10ml')
            ->set('productContext.price', 1.50)
            ->set('productContext.status', true)
            ->set('isMedicine', false)
            ->call('saveProduct')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('products', ['name' => 'Jeringa', 'price' => 1.50]);
    }

    public function test_admin_can_create_medicine()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $group = Group::create(['name' => 'Analgésicos']);

        Livewire::actingAs($admin)->test(ProductManager::class)
            ->set('productContext.name', 'Tafirol')
            ->set('productContext.description', '1g')
            ->set('productContext.price', 500.0)
            ->set('productContext.status', true)
            ->set('isMedicine', true)
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
            'group_id' => $group->id,
            'level' => 'Alta',
        ]);
    }

    public function test_admin_can_edit_product()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $product = Product::create(['name' => 'OldName', 'description' => 'OldDesc', 'price' => 10, 'status' => true]);

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

        $product = Product::create(['name' => 'ToDelete', 'description' => 'To Soft Delete', 'price' => 10, 'status' => true]);

        Livewire::actingAs($admin)->test(ProductManager::class)
            ->call('confirmDeactivate', $product->id)
            ->call('deactivateProduct')
            ->assertHasNoErrors();

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }
}
