<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Livewire\Admin\BackupManager;

class AutoBackupCommand extends Command
{
    protected $signature = 'app:auto-backup';
    protected $description = 'Ejecuta el backup automático según la configuración del usuario';

    public function handle()
    {
        // Instanciamos el manager para reutilizar TODA tu lógica
        $manager = new BackupManager();
        
        // Obtenemos el destino configurado
        $destination = \App\Models\Setting::where('key', 'backup_auto_destination')->first()?->value ?? 'local';
        
        $this->info("Iniciando backup automático hacia: $destination");
        
        // Seteamos el destino en el componente y ejecutamos
        $manager->destination = $destination;
        $manager->createInternalBackup();

        $this->info('Backup automático finalizado con éxito.');
    }
}