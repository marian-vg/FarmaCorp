<?php

use App\Models\Batch;
use App\Models\Group;
use App\Models\Medicine;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Basic setup
    if (! Role::where('name', 'admin')->exists()) {
        Role::create(['name' => 'admin']);
    }

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    // Create a group and two distinct products
    $group = Group::factory()->create(['name' => 'Analgesics']);

    $productA = Product::factory()->create(['name' => 'Generic Paracetamol']);
    $productB = Product::factory()->create(['name' => 'Generic Ibuprofen']);

    // Create medicines for these products
    $this->medicineA = Medicine::factory()->create([
        'product_id' => $productA->id,
        'group_id' => $group->id,
        'presentation_name' => 'Paracetamol 500mg',
        'is_psychotropic' => false,
    ]);

    // Crucial mapping issue: What if another medicine gets an ID matching something else?
    $this->medicineB = Medicine::factory()->create([
        'product_id' => $productB->id,
        'group_id' => $group->id,
        'presentation_name' => 'Ibuprofen 400mg',
        'is_psychotropic' => false,
    ]);

    // Create separate batches for both medicines
    $this->batchA = Batch::factory()->create([
        'medicine_id' => $this->medicineA->id,
        'batch_number' => 'LT-A01',
        'initial_quantity' => 100,
        'current_quantity' => 100,
        'expiration_date' => now()->addYear(),
    ]);

    $this->batchB = Batch::factory()->create([
        'medicine_id' => $this->medicineB->id,
        'batch_number' => 'LT-B02',
        'initial_quantity' => 50,
        'current_quantity' => 50,
        'expiration_date' => now()->addYear(),
    ]);

    // Create stock movements for both
    StockMovement::create([
        'batch_id' => $this->batchA->id,
        'type' => 'ingreso',
        'quantity' => 100,
        'reason' => 'compra',
        'user_id' => $this->admin->id,
    ]);

    StockMovement::create([
        'batch_id' => $this->batchB->id,
        'type' => 'ingreso',
        'quantity' => 50,
        'reason' => 'compra',
        'user_id' => $this->admin->id,
    ]);
});

it('lists stock movements correctly without duplicate joins or incorrect medicine assignments', function () {
    Livewire::actingAs($this->admin)
        ->test(\App\Livewire\Admin\StockHistorialManager::class)
        ->assertStatus(200)
        ->assertViewHas('movements', function ($movements) {
            // There should be exactly 2 movements in the list
            if ($movements->total() !== 2) {
                return false;
            }

            $movementNames = $movements->map(function ($mov) {
                return $mov->batch->medicine->presentation_name;
            })->toArray();

            // Make sure both distinctly appear and no duplicates due to a bad join
            return in_array('Paracetamol 500mg', $movementNames) &&
                   in_array('Ibuprofen 400mg', $movementNames);
        });
});
