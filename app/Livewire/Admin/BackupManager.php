<?php

namespace App\Livewire\Admin;

use App\Mail\BackupMail;
use App\Traits\Notifies;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Spatie\DbDumper\Databases\PostgreSql;
use Spatie\Permission\PermissionRegistrar;

class BackupManager extends Component
{
    use Notifies;

    public $backups = [];

    public $destination = 'local';

    public function mount()
    {
        // En routes/web.php la ruta ya debe estar protegida por 'admin-backup.acceder', pero si es componente Livewire puro sin ruta dedicada con middleware, verificamos aquí.
        if (! auth()->user()->can('admin-backup.acceder')) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }
        $this->cargarListaBackups();
    }

    public function cargarListaBackups()
    {
        if (! Storage::exists('backups')) {
            Storage::makeDirectory('backups');
        }
        $files = Storage::files('backups');
        $this->backups = collect($files)->map(function ($path) {
            return [
                'name' => basename($path),
                'size' => round(Storage::size($path) / 1024, 2).' KB',
                'date' => date('d/m/Y H:i:s', Storage::lastModified($path)),
            ];
        })->sortByDesc('date')->values()->all();
    }

    public function createInternalBackup()
    {
        if (! auth()->user()->can('admin-backup.crear')) {
            $this->notify('No tienes permisos para crear copias de seguridad.', 'error');
            return;
        }

        try {
            set_time_limit(0);

            $fileName = 'Backup_'.now()->format('Y-m-d_H-i-s').'.sql';
            $storagePath = 'backups/'.$fileName;

            if (! Storage::disk('local')->exists('backups')) {
                Storage::disk('local')->makeDirectory('backups');
            }

            $fullPath = Storage::disk('local')->path($storagePath);

            PostgreSql::create()
                ->setDbName(config('database.connections.pgsql.database'))
                ->setUserName(config('database.connections.pgsql.username'))
                ->setPassword(config('database.connections.pgsql.password'))
                ->setHost(config('database.connections.pgsql.host', '127.0.0.1'))
                ->setPort(config('database.connections.pgsql.port', '5432'))
                ->excludeTables([
                    'migrations',
                    'jobs',
                    'job_batches',
                    'failed_jobs',
                    'cache',
                    'cache_locks',
                    'sessions'
                ])
                ->addExtraOption('--data-only')
                ->addExtraOption('--inserts')
                ->dumpToFile($fullPath);

            if ($this->destination === 'email' || $this->destination === 'all') {
                $this->sendToEmail($fullPath, $fileName);
            }

            if ($this->destination === 'supabase' || $this->destination === 'all') {
                $sqlDump = Storage::disk('local')->get($storagePath);
                $this->uploadToCloud($fileName, $sqlDump);
            }

            $this->cargarListaBackups();
            $this->notify('Backup de datos generado correctamente.', 'success');

        } catch (\Exception $e) {
            $this->notify('Fallo en la operación: '.$e->getMessage(), 'error');
        }
    }

    private function sendToEmail($path, $name)
    {
        $user = auth()->user();

        if (! $user) {
            throw new \Exception('No hay una sesión activa para enviar el correo.');
        }

        $userName = $user->first_name ?? $user->name ?? 'Administrador';

        Mail::to($user->email)->send(new BackupMail($path, $name, $userName));
    }

    private function uploadToCloud($name, $content)
    {
        try {
            // Usamos el disco 'supabase' que configuramos recién
            // Guardamos el contenido del SQL directamente en la nube
            Storage::disk('supabase')->put($name, $content);
            
            // Opcional: Podrías registrar en un log que la subida fue exitosa
        } catch (\Exception $e) {
            // Si la nube falla, lanzamos el error para que la notificación lo muestre
            throw new \Exception("Error al subir a Supabase: " . $e->getMessage());
        }
    }

    private function sincronizarSecuencias()
    {
        $sequences = DB::select("
            SELECT table_name, column_name
            FROM information_schema.columns
            WHERE table_schema = 'public'
              AND column_default LIKE 'nextval%'
        ");

        foreach ($sequences as $seq) {
            $tableName = $seq->table_name;
            $columnName = $seq->column_name;

            if (!\Illuminate\Support\Facades\Schema::hasTable($tableName)) {
                continue;
            }

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
        if (! auth()->user()->can('admin-backup.restaurar')) {
            $this->notify('No tienes permisos críticos para restaurar el sistema.', 'error');
            return;
        }

        try {
            $sql = Storage::get('backups/'.$fileName);

            DB::transaction(function () use ($sql) {
                DB::statement("SET session_replication_role = 'replica';");

                $excludedTables = [
                    'migrations',
                    'jobs',
                    'job_batches',
                    'failed_jobs',
                    'cache',
                    'cache_locks',
                    'sessions'
                ];

                $tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE'");
                
                // Filtramos para limpiar SOLO las tablas de negocio (batches, clients, medicines, etc.)
                $tableNames = collect($tables)
                    ->filter(fn($t) => !in_array($t->table_name, $excludedTables))
                    ->map(fn($t) => '"' . $t->table_name . '"')
                    ->toArray();

                if (!empty($tableNames)) {
                    DB::statement("TRUNCATE TABLE " . implode(', ', $tableNames) . " RESTART IDENTITY CASCADE;");
                }

                // Inyectamos los datos limpios
                DB::unprepared($sql);
                
                DB::statement("SET session_replication_role = 'origin';");
                $this->sincronizarSecuencias();
            });

            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            $this->notify('Sistema restaurado al estado de '.$fileName, 'success');
        } catch (\Exception $e) {
            $this->notify('Error al restaurar: '.$e->getMessage(), 'error');
        }
    }

    public function deleteBackup($fileName)
    {
        if (! auth()->user()->can('admin-backup.eliminar')) {
            $this->notify('No tienes permisos para eliminar copias de seguridad.', 'error');

            return;
        }

        Storage::delete('backups/'.$fileName);
        $this->cargarListaBackups();
        $this->notify('Archivo de respaldo eliminado.', 'warning');
    }

    public function render()
    {
        return view('livewire.admin.backup-manager')->layout('components.layouts.app', ['title' => 'Gestor de Resguardos']);
    }
}
