<?php

namespace App\Livewire\Admin;

use App\Traits\Notifies;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Mail\BackupMail;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class BackupManager extends Component
{
    use Notifies;

    public $backups = [];
    public $destination = 'local';

    public function mount()
    {
        $this->cargarListaBackups();
    }

    public function cargarListaBackups()
    {
        if (!Storage::exists('backups')) { Storage::makeDirectory('backups'); }
        $files = Storage::files('backups');
        $this->backups = collect($files)->map(function($path) {
            return [
                'name' => basename($path),
                'size' => round(Storage::size($path) / 1024, 2).' KB',
                'date' => date('d/m/Y H:i:s', Storage::lastModified($path)),
            ];
        })->sortByDesc('date')->values()->all();
    }

    public function createInternalBackup()
    {
        try {
            set_time_limit(0);
            
            $sqlDump = $this->generateSqlContent();
            $fileName = 'Backup_' . now()->format('Y-m-d_H-i-s') . '.sql';
            $storagePath = 'backups/' . $fileName;

            // Guardamos el archivo
            Storage::put($storagePath, $sqlDump);
            
            // FIX: Obtenemos la ruta absoluta de forma segura
            $fullPath = Storage::path($storagePath);

            if ($this->destination === 'email' || $this->destination === 'all') {
                // Pasamos también el nombre del usuario para el mail
                $this->sendToEmail($fullPath, $fileName);
            }

            if ($this->destination === 'supabase' || $this->destination === 'all') {
                $this->uploadToCloud($fileName, $sqlDump);
            }

            $this->cargarListaBackups();
            $this->notify('Backup generado y distribuido correctamente.', 'success');

        } catch (\Exception $e) {
            $this->notify('Fallo en la operación: ' . $e->getMessage(), 'error');
        }
    }

    private function generateSqlContent()
    {
        $tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE'");
        $tableNames = collect($tables)->filter(fn($t) => $t->table_name !== 'migrations')->map(fn($t) => '"' . $t->table_name . '"')->toArray();

        $sqlDump = "-- FarmaCorp Security Backup\nSET session_replication_role = 'replica';\n\n";
        
        if (!empty($tableNames)) {
            $sqlDump .= "TRUNCATE TABLE " . implode(', ', $tableNames) . " RESTART IDENTITY CASCADE;\n\n";
        }

        foreach ($tables as $table) {
            if ($table->table_name === 'migrations') continue;
            $rows = DB::table($table->table_name)->get();
            foreach ($rows as $row) {
                $rowArray = (array) $row;
                $columns = array_keys($rowArray);
                $values = array_map(fn($v) => is_null($v) ? 'NULL' : (is_bool($v) ? ($v ? 'true' : 'false') : DB::getPdo()->quote($v)), array_values($rowArray));
                $sqlDump .= "INSERT INTO \"{$table->table_name}\" (\"" . implode('", "', $columns) . "\") VALUES (" . implode(', ', $values) . ");\n";
            }
        }
        $sqlDump .= "\nSET session_replication_role = 'origin';";
        return $sqlDump;
    }

    private function sendToEmail($path, $name)
    {
        $user = auth()->user();

        // Verificación de seguridad
        if (!$user) {
            throw new \Exception("No hay una sesión activa para enviar el correo.");
        }

        // Si el usuario no tiene first_name, usamos el email o un genérico
        $userName = $user->first_name ?? $user->name ?? 'Administrador';

        Mail::to($user->email)->send(new BackupMail($path, $name, $userName));
    }

    private function uploadToCloud($name, $content)
    {
        // Simulamos Supabase Storage usando un disco dedicado o carpeta externa
        Storage::disk('local')->put('supabase_cloud_mock/' . $name, $content);
    }

    private function sincronizarSecuencias()
    {
        // Consultamos solo las tablas y columnas que tienen un valor por defecto autoincremental (nextval)
        $sequences = DB::select("
            SELECT table_name, column_name
            FROM information_schema.columns
            WHERE table_schema = 'public'
              AND column_default LIKE 'nextval%'
        ");

        foreach ($sequences as $seq) {
            $tableName = $seq->table_name;
            $columnName = $seq->column_name;

            if ($tableName === 'migrations') {
                continue;
            }

            // Sincronizamos la secuencia detectada con el valor máximo real de esa columna específica
            DB::statement("
                SELECT setval(
                    pg_get_serial_sequence('\"$tableName\"', '$columnName'), 
                    coalesce(max(\"$columnName\"), 1)
                ) FROM \"$tableName\"
            ");
        }
    }

    public function restoreFromDisk($fileName)
    {
        try {
            $sql = Storage::get('backups/'.$fileName);

            DB::transaction(function () use ($sql) {
                DB::statement("SET session_replication_role = 'replica';");
                DB::unprepared($sql);
                DB::statement("SET session_replication_role = 'origin';");
                $this->sincronizarSecuencias();
            });

            $this->notify('Sistema restaurado al estado de '.$fileName, 'success');
        } catch (\Exception $e) {
            $this->notify('Error al restaurar: '.$e->getMessage(), 'danger');
        }
    }

    public function deleteBackup($fileName)
    {
        Storage::delete('backups/'.$fileName);
        $this->cargarListaBackups();
        $this->notify('Archivo de respaldo eliminado.', 'warning');
    }

    public function render()
    {
        return view('livewire.admin.backup-manager')->layout('layouts.app');
    }
}
