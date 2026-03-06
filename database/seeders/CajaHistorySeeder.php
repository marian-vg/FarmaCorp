<?php

namespace Database\Seeders;

use App\Models\Caja;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CajaHistorySeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos un usuario para asignar las cajas (el primero que encuentre)
        $user = User::first();

        if (!$user) {
            $this->command->error('No hay usuarios en la base de datos para asignar las cajas.');
            return;
        }

        $this->command->info('Generando historial de cajas para los últimos 7 días...');

        // Generamos datos para los últimos 7 días
        for ($i = 6; $i >= 0; $i--) {
            $fecha = Carbon::now()->subDays($i);
            
            // Creamos 2 cajas por día para que el gráfico sume valores interesantes
            for ($j = 1; $j <= 2; $j++) {
                $montoInicial = rand(1000, 5000);
                $recaudacionNeta = rand(5000, 15000);
                $montoFinal = $montoInicial + $recaudacionNeta;

                Caja::create([
                    'user_id' => $user->id,
                    'monto_inicial' => $montoInicial,
                    'monto_final' => $montoFinal,
                    'fecha_apertura' => $fecha->copy()->setTime(8, 0, 0), // Abre a las 8am
                    'fecha_cierre' => $fecha->copy()->setTime(18, 0, 0),  // Cierra a las 6pm
                    'observaciones' => "Cierre automático de prueba - Día -$i turno $j",
                ]);
            }
        }

        $this->command->info('¡Historial generado con éxito!');
    }
}