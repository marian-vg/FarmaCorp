<?php

namespace Tests\Feature\Admin;

use App\Events\StockActualizado;
use App\Livewire\Admin\StockEgresoManager;
use App\Models\Batch;
use App\Models\Group;
use App\Models\Medicine;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo('stock.egreso');

    $this->group = Group::factory()->create();

    $this->product = Product::create([
        'name' => 'Dipirona Test',
        'description' => 'Test Desc',
        'status' => true,
    ]);

    $this->medicine = Medicine::create([
        'product_id' => $this->product->id,
        'group_id' => $this->group->id,
        'presentation_name' => 'Dipirona 500mg Test',
        'price' => 1000,
    ]);

    $this->batch = Batch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'LOTE-TEST-EGRESO',
        'expiration_date' => now()->addYear(),
        'initial_quantity' => 100,
        'current_quantity' => 100,
        'minimum_stock' => 10,
    ]);

    $this->stock = Stock::create([
        'medicine_id' => $this->medicine->id,
        'cantidad_actual' => 100,
        'stock_minimo' => 10,
    ]);
});

it('descuenta el stock correctamente, crea el movimiento y emite el evento StockActualizado', function () {
    // 1. Fake events to intercept the broadcast
    Event::fake([
        StockActualizado::class,
    ]);

    // 2. Arrange: we want to subtract 5 units for merma_rotura
    Livewire::actingAs($this->admin)
        ->test(StockEgresoManager::class)
        ->call('selectBatch', $this->batch->id, $this->batch->current_quantity)
        ->set('quantity_to_remove', 5)
        ->set('reason', 'merma_rotura')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('notify');

    // 3. Assert Database states (Stock movements)
    $this->assertDatabaseHas('batches', [
        'id' => $this->batch->id,
        'current_quantity' => 95,
    ]);

    $this->assertDatabaseHas('stocks', [
        'medicine_id' => $this->medicine->id,
        'cantidad_actual' => 95,
    ]);

    $this->assertDatabaseHas('stock_movements', [
        'batch_id' => $this->batch->id,
        'quantity' => 5,
        'type' => 'egreso',
        'reason' => 'merma', // mapped reason from StockEgresoManager
    ]);

    // 4. Assert Livewire Broadcast Event was dispatched so Reverb catches it
    Event::assertDispatched(StockActualizado::class);
});

it('no permite egresar mas stock del que hay en el lote', function () {
    Livewire::actingAs($this->admin)
        ->test(StockEgresoManager::class)
        ->call('selectBatch', $this->batch->id, $this->batch->current_quantity)
        ->set('quantity_to_remove', 150) // More than the 100 available
        ->set('reason', 'merma_rotura')
        ->call('save')
        ->assertHasErrors(['quantity_to_remove']);
});
