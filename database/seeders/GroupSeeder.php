<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            \App\Models\Group::firstOrCreate(
                ['name' => $name],
                ['description' => "Categoría de medicamentos: {$name}"]
            );
        }

        \App\Models\Group::factory(5)->create();
    }
}
