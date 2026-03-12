<?php

use App\Livewire\Admin\StockEgresoManager;
use App\Models\Batch;
use App\Models\Group;
use App\Models\Medicine;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StockEgresoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin']);
    }

    public function test_admin_can_search_and_register_stock_egress()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $group = Group::create(['name' => 'Painkillers']);
        $product = Product::factory()->create(['name' => 'Paracetamol']);
        $medicine = Medicine::create(['product_id' => $product->id, 'group_id' => $group->id, 'price' => 10]);

        // Create initial batch with 10 units
        $batch = Batch::create([
            'medicine_id' => $medicine->product_id,
            'batch_number' => 'LOTE-PARA',
            'expiration_date' => now()->addDays(100),
            'initial_quantity' => 10,
            'current_quantity' => 10,
            'minimum_stock' => 5,
        ]);

        // Step 1: Simulate trying to extract 15 units (should fail)
        Livewire::actingAs($admin)->test(StockEgresoManager::class)
            ->call('selectBatch', $batch->id, $batch->current_quantity)
            ->assertSet('batch_id', $batch->id)
            ->set('quantity_to_remove', 15)
            ->set('reason', 'merma_rotura')
            ->call('save')
            ->assertHasErrors(['quantity_to_remove']); // Rule validation error max:10

        // Manually override validation just to test the backend hard block (optional, but UI stops it first)

        // Step 2: Extract 3 units successfully
        Livewire::actingAs($admin)->test(StockEgresoManager::class)
            ->call('selectBatch', $batch->id, $batch->current_quantity)
            ->set('quantity_to_remove', 3)
            ->set('reason', 'merma_rotura')
            ->call('save')
            ->assertHasNoErrors();

        // Assert Batch subtraction
        $this->assertDatabaseHas('batches', [
            'id' => $batch->id,
            'current_quantity' => 7, // 10 original - 3 extracted
        ]);

        // Assert audited Stock Movement
        $this->assertDatabaseHas('stock_movements', [
            'batch_id' => $batch->id,
            'user_id' => $admin->id,
            'type' => 'egreso',
            'reason' => 'merma',
            'quantity' => 3,
        ]);
    }
}
