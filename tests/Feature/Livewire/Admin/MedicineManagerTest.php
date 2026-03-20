<?php

namespace Tests\Feature\Livewire\Admin;

use App\Livewire\Admin\MedicineManager;
use App\Models\Group;
use App\Models\Medicine;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MedicineManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'empleado']);
    }

    public function test_admin_can_access_medicine_manager()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.medicines'))
            ->assertSuccessful();
    }

    public function test_renders_medicines()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $product = Product::factory()->create(['name' => 'Amoxicilina']);
        $group = Group::create(['name' => 'Antibióticos']);

        Medicine::create([
            'product_id' => $product->id,
            'group_id' => $group->id,
            'price' => 15.00,
            'level' => '500mg',
            'is_psychotropic' => false,
        ]);

        Livewire::actingAs($admin)->test(MedicineManager::class)
            ->assertSee('Amoxicilina')
            ->assertSee('Antibióticos')
            ->assertSee('500mg');
    }

    public function test_creates_medicine_from_existing_product()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $product = Product::factory()->create(['name' => 'Clonazepam']);
        $group = Group::create(['name' => 'Ansiolíticos']);

        Livewire::actingAs($admin)->test(MedicineManager::class)
            ->call('createMedicine') // triggers modal and resets context
            ->set('context.product_id', $product->id)
            ->set('context.group_id', $group->id)
            ->set('context.price', 50.00)
            ->set('context.level', '2mg')
            ->set('context.leaflet', 'Tomar una pastilla antes de dormir.')
            ->set('context.is_psychotropic', true)
            ->call('saveMedicine')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('medicines', [
            'product_id' => $product->id,
            'group_id' => $group->id,
            'price' => 50.00,
            'level' => '2mg',
            'is_psychotropic' => true,
        ]);
    }

    public function test_shows_only_available_products_for_creation()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $availableProduct = Product::factory()->create(['name' => 'Ibuprofeno', 'status' => true]);

        $productWithMedicine = Product::factory()->create(['name' => 'Paracetamol', 'status' => true]);
        $group = Group::create(['name' => 'Analgésicos']);
        Medicine::create(['product_id' => $productWithMedicine->id, 'group_id' => $group->id, 'price' => 10, 'level' => '1g', 'is_psychotropic' => false]);

        $inactiveProduct = Product::factory()->create(['name' => 'Descontinuado', 'status' => false]);

        Livewire::actingAs($admin)->test(MedicineManager::class)
            ->assertSee('Ibuprofeno') // Available selection
            ->assertDontSee($inactiveProduct->name); // Except in the general text, but let's check it's not in the selection. Actually, the Livewire assertSee checks the whole HTML.

        // Let's assert from a different angle to be certain: Check the component's rendered data properties
        $component = Livewire::actingAs($admin)->test(MedicineManager::class);
        $availableProducts = $component->viewData('availableProducts');

        $this->assertTrue($availableProducts->contains('id', $availableProduct->id));
        $this->assertFalse($availableProducts->contains('id', $productWithMedicine->id));
        $this->assertFalse($availableProducts->contains('id', $inactiveProduct->id));
    }

    public function test_can_filter_psychotropic_medicines()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $prod1 = Product::factory()->create(['name' => 'NormalMed']);
        $prod2 = Product::factory()->create(['name' => 'PsychoMed']);
        $group = Group::create(['name' => 'General']);

        Medicine::create(['product_id' => $prod1->id, 'group_id' => $group->id, 'price' => 10, 'is_psychotropic' => false]);
        Medicine::create(['product_id' => $prod2->id, 'group_id' => $group->id, 'price' => 10, 'is_psychotropic' => true]);

        Livewire::actingAs($admin)->test(MedicineManager::class)
            ->assertSee('NormalMed')
            ->assertSee('PsychoMed')
            ->set('filterPsychotropic', true)
            ->assertDontSee('NormalMed')
            ->assertSee('PsychoMed');
    }

    public function test_can_load_leaflet_details_into_modal()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $prod = Product::factory()->create(['name' => 'LeafletMed']);
        $group = Group::create(['name' => 'General']);
        $medicine = Medicine::create([
            'product_id' => $prod->id,
            'group_id' => $group->id,
            'price' => 10,
            'level' => '100mg',
            'leaflet' => 'Este es el texto del prospecto clínico.',
        ]);

        Livewire::actingAs($admin)->test(MedicineManager::class)
            ->call('viewLeaflet', $medicine->id)
            ->assertSet('viewingMedicine.id', $medicine->id)
            ->assertSee('Este es el texto del prospecto clínico.');
    }

    public function test_expiration_date_badges_render_correctly()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $prodExpired = Product::factory()->create(['name' => 'MedVencido']);
        $prodWarning = Product::factory()->create(['name' => 'MedPorVencer']);
        $prodGood = Product::factory()->create(['name' => 'MedOk']);
        $group = Group::create(['name' => 'General']);

        Medicine::create(['product_id' => $prodExpired->id, 'group_id' => $group->id, 'price' => 10, 'expiration_date' => now()->subDays(5)]);
        Medicine::create(['product_id' => $prodWarning->id, 'group_id' => $group->id, 'price' => 10, 'expiration_date' => now()->addDays(15)]);
        Medicine::create(['product_id' => $prodGood->id, 'group_id' => $group->id, 'price' => 10, 'expiration_date' => now()->addDays(60)]);

        $html = Livewire::actingAs($admin)->test(MedicineManager::class)->html();
        Livewire::actingAs($admin)->test(MedicineManager::class)
            ->assertSee('Vencido:')
            ->assertSee('Vence pronto:')
            ->assertSee('Vence:');
    }
}
