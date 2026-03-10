<?php

use App\Livewire\Admin\StockIngresoManager;
use App\Models\Batch;
use App\Models\Group;
use App\Models\Medicine;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StockIngresoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin']);
    }

    public function test_admin_can_search_and_register_medicine_stock_entry()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $group = Group::create(['name' => 'Antibiotics']);
        $product = Product::factory()->create(['name' => 'Amoxicillin']);
        $medicine = Medicine::create(['product_id' => $product->id, 'group_id' => $group->id, 'price' => 10]);

        $futureDate = now()->addDays(365)->format('Y-m-d');

        Livewire::actingAs($admin)->test(StockIngresoManager::class)
            // Assert searchable component loads
            ->assertSee('Amoxicillin')
            // Simulate medicine selection
            ->call('selectMedicine', $medicine->product_id)
            ->assertSet('medicine_id', $medicine->product_id)
            // Fill the form
            ->set('batch_number', 'LOTE-1234')
            ->set('expiration_date', $futureDate)
            ->set('quantity_received', 100)
            ->set('minimum_stock', 15)
            // Save transactionally
            ->call('save')
            ->assertHasNoErrors();

        // Verify DB integrity for Batch (Lote)
        $this->assertDatabaseHas('batches', [
            'medicine_id' => $medicine->product_id,
            'batch_number' => 'LOTE-1234',
            'expiration_date' => clone Carbon::parse($futureDate)->startOfDay(),
            'initial_quantity' => 100,
            'current_quantity' => 100, // Should be same as initial right after creation
            'minimum_stock' => 15,
        ]);

        $batchId = Batch::where('batch_number', 'LOTE-1234')->first()->id;

        // Verify DB integrity for Stock Movement (MovimientoStock) ensuring 'ingreso' and 'compra'
        $this->assertDatabaseHas('stock_movements', [
            'batch_id' => $batchId,
            'user_id' => $admin->id,
            'type' => 'ingreso',
            'reason' => 'compra',
            'quantity' => 100,
        ]);
    }
}
