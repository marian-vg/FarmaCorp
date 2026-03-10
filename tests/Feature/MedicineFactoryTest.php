<?php

namespace Tests\Feature;

use App\Models\Medicine;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a medicine using the factory', function () {
    $medicine = Medicine::factory()->create();

    expect($medicine)->toBeInstanceOf(Medicine::class)
        ->and($medicine->product_id)->not->toBeNull()
        ->and($medicine->level)->not->toBeEmpty()
        ->and($medicine->leaflet)->not->toBeEmpty()
        ->and($medicine->group_id)->not->toBeNull()
        ->and($medicine->product)->not->toBeNull();

    $this->assertDatabaseHas('medicines', [
        'product_id' => $medicine->product_id,
        'level' => $medicine->level
    ]);
});

it('can run the medicine seeder successfully', function () {
    $this->seed(\Database\Seeders\VademecumSeeder::class);
    
    expect(Medicine::count())->toBe(15);
});
