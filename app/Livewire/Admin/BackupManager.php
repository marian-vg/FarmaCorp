<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use App\Traits\Notifies;
use Illuminate\Support\Facades\Schema;

class BackupManager extends Component
{
    use WithFileUploads, Notifies;

    public $backupFile;

    public function downloadBackup()
    {
        try {
            set_time_limit(0); // Evita que PHP corte el proceso si hay muchos datos
            
            $tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE'");
            $sqlDump = "-- FarmaCorp Professional Backup\n";
            $sqlDump .= "-- Generado el: " . now()->format('d/m/Y H:i:s') . "\n\n";
            $sqlDump .= "SET session_replication_role = 'replica';\n\n";

            foreach ($tables as $table) {
                $tableName = $table->table_name;
                
                // Evitamos respaldar la tabla de migraciones para no romper Laravel al restaurar
                if ($tableName == 'migrations') continue;

                $sqlDump .= "-- Estructura y Datos de la tabla: $tableName\n";
                $sqlDump .= "TRUNCATE TABLE \"$tableName\" RESTART IDENTITY CASCADE;\n";

                // Obtenemos los registros
                $rows = DB::table($tableName)->get();

                foreach ($rows as $row) {
                    $rowArray = (array) $row;
                    $columns = array_keys($rowArray);
                    $values = array_map(function ($value) {
                        if (is_null($value)) return 'NULL';
                        if (is_bool($value)) return $value ? 'true' : 'false';
                        return DB::getPdo()->quote($value);
                    }, array_values($rowArray));

                    $sqlDump .= "INSERT INTO \"$tableName\" (\"" . implode('", "', $columns) . "\") VALUES (" . implode(', ', $values) . ");\n";
                }
                $sqlDump .= "\n";
            }

            $sqlDump .= "\nSET session_replication_role = 'origin';";

            $fileName = 'FarmaCorp-Backup-' . now()->format('Y-m-d_H-i'). '.sql';
            
            return response()->streamDownload(function () use ($sqlDump) {
                echo $sqlDump;
            }, $fileName);

        } catch (\Exception $e) {
            $this->notify('Error generando backup: ' . $e->getMessage(), 'danger');
        }
    }

    public function restore()
    {
        $this->validate(['backupFile' => 'required|file']);

        try {
            $sql = file_get_contents($this->backupFile->getRealPath());

            DB::transaction(function () use ($sql) {
                // Desactivamos restricciones para que el orden de los INSERT no importe
                DB::statement("SET session_replication_role = 'replica';");
                
                DB::unprepared($sql);
                
                DB::statement("SET session_replication_role = 'origin';");
            });

            $this->notify('¡Punto de restauración aplicado con éxito!', 'success');
            $this->reset('backupFile');
        } catch (\Exception $e) {
            $this->notify('Fallo crítico en restauración: ' . $e->getMessage(), 'danger');
        }
    }

    public function render()
    {
        return view('livewire.admin.backup-manager')->layout('layouts.app');
    }
}