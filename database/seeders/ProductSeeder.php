<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Creamos 20 productos activos para el catálogo principal
        Product::factory()->count(20)->create();

        // Creamos 5 productos inactivos para probar la pestaña de Archivo (RF-02)
        Product::factory()->count(5)->inactive()->create();
    }
}