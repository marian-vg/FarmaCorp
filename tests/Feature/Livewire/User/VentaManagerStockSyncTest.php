<?php

declare(strict_types=1);

use App\Livewire\Admin\StockEgresoManager;
use App\Livewire\Admin\StockIngresoManager;
use App\Livewire\User\VentaManager;
use App\Models\Batch;
use App\Models\Group;
use App\Models\Medicine;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    if (! Role::where('name', 'admin')->exists()) {
        Role::create(['name' => 'admin']);
    }
    if (! Role::where('name', 'empleado')->exists()) {
        Role::create(['name' => 'empleado']);
    }

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->employee = User::factory()->create();
    $this->employee->assignRole('empleado');

    $group = Group::factory()->create();
    $product = Product::factory()->create(['name' => 'Paracetamol Test', 'status' => true]);

    $this->medicine = Medicine::factory()->create([
        'product_id' => $product->id,
        'group_id' => $group->id,
        'presentation_name' => 'Paracetamol 500mg x 20',
        'is_psychotropic' => false,
    ]);

    $this->batch = Batch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'SYNC-TEST-001',
        'initial_quantity' => 50,
        'current_quantity' => 50,
        'expiration_date' => now()->addYear(),
        'minimum_stock' => 5,
    ]);

    Stock::create([
        'medicine_id' => $this->medicine->id,
        'cantidad_actual' => 50,
        'stock_minimo' => 5,
    ]);
});

it('dispatches stock-actualizado event when stock ingress is saved', function () {
    Livewire::actingAs($this->admin)
        ->test(StockIngresoManager::class)
        ->set('medicine_id', $this->medicine->id)
        ->set('batch_number', 'TEST-001')
        ->set('expiration_date', now()->addYear()->format('Y-m-d'))
        ->set('quantity_received', 10)
        ->set('minimum_stock', 5)
        ->call('save')
        ->assertDispatched('stock-actualizado');
});

it('dispatches stock-actualizado event when stock egress is saved', function () {
    Livewire::actingAs($this->admin)
        ->test(StockEgresoManager::class)
        ->set('batch_id', $this->batch->id)
        ->set('current_stock_display', 50)
        ->set('quantity_to_remove', 5)
        ->set('reason', 'merma_rotura')
        ->call('save')
        ->assertDispatched('stock-actualizado');
});

it('VentaManager listens to stock-actualizado and refreshes', function () {
    Livewire::actingAs($this->employee)
        ->test(VentaManager::class)
        ->dispatch('stock-actualizado')
        ->assertStatus(200);
});
