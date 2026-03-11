<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Traits\Notifies;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class BackupManager extends Component
{
    use Notifies;

    public $backups = [];

    public function mount()
    {
        $this->cargarListaBackups();
    }

    public function cargarListaBackups()
    {
        // Aseguramos que la carpeta exista
        if (!Storage::exists('backups')) {
            Storage::makeDirectory('backups');
        }

        // Leemos los archivos y los ordenamos por fecha (el más nuevo primero)
        $files = Storage::files('backups');
        
        $this->backups = collect($files)->map(function($path) {
            return [
                'name' => basename($path),
                'size' => round(Storage::size($path) / 1024, 2) . ' KB',
                'date' => date('d/m/Y H:i:s', Storage::lastModified($path)),
                'raw_path' => $path
            ];
        })->sortByDesc('date')->values()->all();
    }

    public function createInternalBackup()
    {
        try {
            set_time_limit(0);
            
            // Reutilizamos tu lógica de generación de SQL puro PHP
            $tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE'");
            $sqlDump = "SET session_replication_role = 'replica';\n\n";

            foreach ($tables as $table) {
                $tableName = $table->table_name;
                if ($tableName == 'migrations') continue;

                $sqlDump .= "TRUNCATE TABLE \"$tableName\" RESTART IDENTITY CASCADE;\n";
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
            }
            $sqlDump .= "\nSET session_replication_role = 'origin';";

            // GUARDAR EN DISCO EN VEZ DE DESCARGAR
            $fileName = 'Backup_' . now()->format('Y-m-d_H-i-s') . '.sql';
            Storage::put('backups/' . $fileName, $sqlDump);

            $this->cargarListaBackups();
            $this->notify('Punto de restauración creado con éxito.', 'success');

        } catch (\Exception $e) {
            $this->notify('Error: ' . $e->getMessage(), 'danger');
        }
    }

    public function restoreFromDisk($fileName)
    {
        try {
            $sql = Storage::get('backups/' . $fileName);

            DB::transaction(function () use ($sql) {
                DB::statement("SET session_replication_role = 'replica';");
                DB::unprepared($sql);
                DB::statement("SET session_replication_role = 'origin';");
            });

            $this->notify('Sistema restaurado al estado de ' . $fileName, 'success');
        } catch (\Exception $e) {
            $this->notify('Error al restaurar: ' . $e->getMessage(), 'danger');
        }
    }

    public function deleteBackup($fileName)
    {
        Storage::delete('backups/' . $fileName);
        $this->cargarListaBackups();
        $this->notify('Archivo de respaldo eliminado.', 'warning');
    }

    public function render()
    {
        return view('livewire.admin.backup-manager')->layout('layouts.app');
    }
}