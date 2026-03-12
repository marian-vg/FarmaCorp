<?php

namespace Database\Seeders;

use App\Models\MedioPago;
use Illuminate\Database\Seeder;

class MedioPagoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medios = [
            ['nombre' => 'Efectivo', 'tipo_medio' => 'Efectivo', 'recargo' => 0, 'descuento' => 0],
            ['nombre' => 'Tarjeta de Débito', 'tipo_medio' => 'Débito', 'recargo' => 0, 'descuento' => 0],
            ['nombre' => 'Tarjeta de Crédito', 'tipo_medio' => 'Crédito', 'recargo' => 0, 'descuento' => 0],
            ['nombre' => 'Vale', 'tipo_medio' => 'Vale', 'recargo' => 0, 'descuento' => 0],
        ];

        foreach ($medios as $medio) {
            MedioPago::updateOrCreate(['nombre' => $medio['nombre']], $medio);
        }
    }
}
