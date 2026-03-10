<div class="space-y-6">
    <header>
        <flux:heading size="xl">Centro de Mantenimiento</flux:heading>
        <flux:subheading>Gestión de respaldos y recuperación ante desastres.</flux:subheading>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- PANEL EXPORTACIÓN --}}
        <flux:card class="flex flex-col justify-between">
            <div class="space-y-4">
                <div class="bg-indigo-100 dark:bg-indigo-900/30 w-12 h-12 rounded-xl flex items-center justify-center text-indigo-600">
                    <flux:icon.cloud-arrow-down />
                </div>
                <div>
                    <flux:heading size="lg">Generar Respaldo</flux:heading>
                    <flux:text size="sm">Crea una copia completa de medicamentos, clientes, ventas y deudas en un archivo .sql.</flux:text>
                </div>
            </div>
            
            <flux:button wire:click="downloadBackup" variant="primary" class="mt-8" icon="arrow-down-tray">
                Descargar Backup Actual
            </flux:button>
        </flux:card>

        {{-- PANEL RESTAURACIÓN (ZONA ROJA) --}}
        <flux:card class="border-red-100 dark:border-red-900/20 bg-red-50/30 dark:bg-red-900/5">
            <div class="space-y-4">
                <div class="bg-red-100 dark:bg-red-900/50 w-12 h-12 rounded-xl flex items-center justify-center text-red-600">
                    <flux:icon.arrow-path />
                </div>
                <div>
                    <flux:heading size="lg" class="text-red-700 dark:text-red-400">Restaurar Base de Datos</flux:heading>
                    <flux:text size="sm" class="text-red-600/70 italic">⚠️ Advertencia: Esta acción reemplazará TODOS los datos actuales por los del archivo seleccionado.</flux:text>
                </div>

                <div class="pt-4 space-y-4">
                    <flux:input type="file" wire:model="backupFile" />
                    
                    <flux:modal.trigger name="confirm-restore">
                        <flux:button variant="danger" class="w-full" icon="exclamation-triangle" :disabled="!$backupFile" wire:loading.attr="disabled">
                            Ejecutar Restauración
                        </flux:button>
                    </flux:modal.trigger>
                </div>
            </div>
        </flux:card>
    </div>

    {{-- MODAL DE CONFIRMACIÓN CRÍTICA --}}
    <flux:modal name="confirm-restore" class="md:w-96 text-center">
        <div class="space-y-6">
            <flux:heading size="lg">¿Estás absolutamente seguro?</flux:heading>
            <flux:text>Esta operación es irreversible. Perderás las ventas realizadas después de la fecha del backup que vas a subir.</flux:text>
            
            <div class="flex gap-2">
                <flux:modal.close><flux:button variant="ghost" class="flex-1">Cancelar</flux:button></flux:modal.close>
                <flux:button variant="danger" class="flex-1" wire:click="restore" @click="Flux.modal('confirm-restore').close()">Sí, restaurar ahora</flux:button>
            </div>
        </div>
    </flux:modal>
</div>