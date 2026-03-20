<?php

namespace Database\Seeders;

use App\Models\Medicine;
use App\Models\ObraSocial;
use Illuminate\Database\Seeder;

class ObraSocialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $obrasSociales = [
            'PAMI',
            'OSDE',
            'OSECAC',
            'Swiss Medical',
            'Galeno',
            'IOMA',
            'Unión Personal',
            'Medicus',
            'Omint',
            'OSDEPYM',
            'Accord Salud',
            'Sancor Salud',
            'Hominis',
            'Prevención Salud',
            'Bristol Medicine',
        ];

        $medicines = Medicine::all();

        foreach ($obrasSociales as $name) {
            $os = ObraSocial::firstOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );

            // Asignar descuentos aleatorios a una selección de medicamentos
            $randomMedicines = $medicines->random(min(15, $medicines->count()));

            foreach ($randomMedicines as $medicine) {
                // Descuentos típicos en Argentina: 40%, 50%, 70%, 100% (PMO)
                $discount = collect([40, 50, 70, 100])->random();

                $os->medicines()->syncWithoutDetaching([
                    $medicine->id => ['discount_percentage' => $discount],
                ]);
            }
        }
    }
}
