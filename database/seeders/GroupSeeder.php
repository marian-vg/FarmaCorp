<?php

namespace Database\Seeders;

use App\Models\Group;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groupNames = ['Analgésicos', 'Antibióticos', 'Venta Libre', 'Cardiología', 'Dermatología', 'Endocrinología', 'Gastroenterología'];

        foreach ($groupNames as $name) {
            Group::firstOrCreate(
                ['name' => $name],
                ['description' => "Categoría de medicamentos: {$name}"]
            );
        }
    }
}
