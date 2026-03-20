<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Medicine;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first();
        if (! $admin) {
            return;
        }

        $medicines = Medicine::all();

        // 1. TANDA DE INGRESOS
        // Seleccionamos 20 medicamentos al azar para inicializar stock
        $selectedMedicines = $medicines->random(min(20, $medicines->count()));

        foreach ($selectedMedicines as $medicine) {
            DB::transaction(function () use ($medicine, $admin) {
                // Generar cantidad de ingreso inicial
                $initialQty = rand(50, 200);
                $minStock = rand(5, 15);

                // Crear Lote
                $batch = Batch::create([
                    'medicine_id' => $medicine->id,
                    'batch_number' => 'LOTE-'.rand(1000, 9999),
                    'expiration_date' => now()->addMonths(rand(6, 24)),
                    'initial_quantity' => $initialQty,
                    'current_quantity' => $initialQty,
                    'minimum_stock' => $minStock,
                ]);

                // Registrar Movimiento de Ingreso
                StockMovement::create([
                    'batch_id' => $batch->id,
                    'user_id' => $admin->id,
                    'type' => 'ingreso',
                    'reason' => 'compra',
                    'quantity' => $initialQty,
                ]);

                // Inicializar/Actualizar Stock Global
                $stock = Stock::firstOrCreate(
                    ['medicine_id' => $medicine->id],
                    ['cantidad_actual' => 0, 'stock_minimo' => 0]
                );

                $stock->cantidad_actual += $initialQty;
                $stock->stock_minimo = $minStock;
                $stock->fecha_actualizacion = now();
                $stock->save();

                // 2. TANDA DE EGRESOS (0% a 30% del stock ingresado)
                // Decidimos aleatoriamente si este medicamento tendrá un egreso inicial
                if (rand(0, 1)) {
                    // Calcular el máximo posible (30%)
                    $maxEgress = (int) floor($initialQty * 0.3);
                    $egressQty = rand(1, $maxEgress);

                    // Restar del lote
                    $batch->current_quantity -= $egressQty;
                    $batch->save();

                    // Restar del stock global
                    $stock->cantidad_actual -= $egressQty;
                    $stock->fecha_actualizacion = now();
                    $stock->save();

                    // Registrar Movimiento de Egreso
                    StockMovement::create([
                        'batch_id' => $batch->id,
                        'user_id' => $admin->id,
                        'type' => 'egreso',
                        'reason' => 'ajuste',
                        'quantity' => $egressQty,
                    ]);
                }
            });
        }
    }
}
