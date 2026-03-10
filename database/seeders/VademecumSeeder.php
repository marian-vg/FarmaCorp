<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Medicine;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class VademecumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = database_path('data/vademecum_mock.json');

        if (! File::exists($jsonPath)) {
            $this->command->warn("El archivo Vademécum {$jsonPath} no existe.");

            return;
        }

        $json = File::get($jsonPath);
        $vademecumData = json_decode($json, true);

        foreach ($vademecumData as $data) {
            // 1. Grupo Farmacológico
            $group = Group::firstOrCreate(
                ['name' => $data['group']],
                ['description' => "Grupo autogenerado para {$data['group']}"]
            );

            // 2. Producto Base (Genérico)
            $product = Product::firstOrCreate(
                ['name' => $data['name']],
                [
                    'description' => $data['description'],
                    'status' => true,
                    'price_updated_at' => now(), // Base temporal inicial
                ]
            );

            // 3. Presentaciones (Medicamentos)
            if (isset($data['medicines']) && is_array($data['medicines'])) {
                foreach ($data['medicines'] as $medData) {
                    Medicine::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'presentation_name' => $medData['presentation_name'],
                        ],
                        [
                            'group_id' => $group->id,
                            'price' => $medData['price'],
                            'level' => $medData['level'],
                            'leaflet' => $medData['leaflet'],
                            'is_psychotropic' => current(array_filter([$medData['is_psychotropic'] ?? false])),
                            'expiration_date' => now()->addYears(2), // Por defecto
                        ]
                    );
                }
            }
        }

        $this->command->info('Vademécum sincronizado a través de Seeder exitosamente.');
    }
}
