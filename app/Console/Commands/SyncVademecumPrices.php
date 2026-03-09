<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\Medicine;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class SyncVademecumPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'farmacorp:sync-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simula el consumo de una API de Vademécum para actualizar los precios de medicinas existentes, aplicando un % de variación aleatorio.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando sincronización simulada de precios desde Vademécum...');

        $jsonPath = database_path('data/vademecum_mock.json');
        if (!File::exists($jsonPath)) {
            $this->error("No se encuentra el archivo JSON base: {$jsonPath}");
            return Command::FAILURE;
        }

        $medicines = Medicine::with('product')->get();
        if ($medicines->isEmpty()) {
            $this->warn('No hay medicamentos en la base de datos para actualizar.');
            return Command::SUCCESS;
        }

        $count = 0;
        
        DB::beginTransaction();
        try {
            foreach ($medicines as $medicine) {
                // Simulación de fluctuación de API: Inflación o descuento aleatorio entre -5% y +15%
                $variationPercentage = rand(-5, 15) / 100;
                $currentPrice = $medicine->price;
                $newPrice = round($currentPrice * (1 + $variationPercentage), 2);

                $medicine->update(['price' => $newPrice]);

                if ($medicine->product) {
                    $medicine->product->update(['price_updated_at' => now()]);
                }
                
                $count++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error durante la actualización: " . $e->getMessage());
            return Command::FAILURE;
        }

        $this->info("¡Sincronización completada! Se han actualizado los precios de {$count} medicamento(s).");
        return Command::SUCCESS;
    }
}
