<?php

use App\Models\Medicine;
use App\Models\Product;
use Database\Seeders\VademecumSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

it('seeds the database with vademecum data without duplicating', function () {
    // Assert DB is empty initially for these
    expect(Medicine::count())->toBe(0);

    // Run the seeder once
    $this->seed(VademecumSeeder::class);
    $initialCount = Medicine::count();
    
    expect($initialCount)->toBeGreaterThan(0);
    
    // Run again to ensure idempotency (no duplicates created)
    $this->seed(VademecumSeeder::class);
    expect(Medicine::count())->toBe($initialCount);
});

it('syncs prices with artisan command', function () {
    // First we need some data seeded
    $this->seed(VademecumSeeder::class);
    
    $medicine = Medicine::first();
    $originalDate = clone $medicine->product->price_updated_at;

    // Simulate time passing
    $this->travel(1)->day();

    // Run command
    Artisan::call('farmacorp:sync-prices');

    $medicine->refresh();
    
    // Since it's a random variation between -5% and 15%, it could rarely be exactly 0, 
    // but the `price_updated_at` WILL change.
    expect($medicine->product->price_updated_at->gt($originalDate))->toBeTrue();
});
