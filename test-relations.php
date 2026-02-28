<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$batch = App\Models\Batch::first();
if ($batch) {
    echo "Batch medicine_id: {$batch->medicine_id}\n";
    $medicine = $batch->medicine;
    echo "Medicine via DB: " . json_encode(DB::table('medicines')->where('product_id', $batch->medicine_id)->first()) . "\n";
    echo "Medicine via relation: " . ($medicine ? $medicine->product_id : 'NULL') . "\n";
    if ($medicine) {
        $product = $medicine->product;
        echo "Product via relation: " . ($product ? $product->name : 'NULL') . "\n";
    }
}
