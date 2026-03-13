<div class="space-y-6">
    <flux:card class="!p-0 overflow-hidden">
        {{-- Banner de Acción --}}
        <div class="p-6 border-b border-zinc-100 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/20 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <flux:heading size="lg">Puntos de Restauración</flux:heading>
                <flux:subheading>Historial de copias de seguridad almacenadas en el servidor.</flux:subheading>
            </div>
            
            <flux:button wire:click="createInternalBackup" icon="plus" variant="primary" class="shadow-lg shadow-indigo-500/20">
                Generar Punto Ahora
            </flux:button>
        </div>

        {{-- Tabla de Backups --}}
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Fecha y Hora</flux:table.column>
                <flux:table.column>Identificador</flux:table.column>
                <flux:table.column>Peso</flux:table.column>
                <flux:table.column align="right">Gestión</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($backups as $backup)
                    <flux:table.row :key="$backup['name']">
                        <flux:table.cell class="font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $backup['date'] }}
                        </flux:table.cell>
                        
                        <flux:table.cell>
                            <flux:badge size="sm" color="zinc" variant="outline" class="font-mono text-[10px]">
                                {{ $backup['name'] }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell class="text-zinc-500 text-xs italic">
                            {{ $backup['size'] }}
                        </flux:table.cell>

                        <flux:table.cell align="right">
                            <div class="flex gap-2 justify-end">
                                <flux:modal.trigger name="confirm-restore-{{ $loop->index }}">
                                    <flux:button size="xs" icon="arrow-path" variant="subtle" color="orange" tooltip="Restaurar este punto" />
                                </flux:modal.trigger>

                                <flux:button size="xs" icon="trash" variant="ghost" color="red" wire:click="deleteBackup('{{ $backup['name'] }}')" tooltip="Eliminar permanentemente" />
                            </div>

                            {{-- MODAL DE CONFIRMACIÓN --}}
                            <flux:modal name="confirm-restore-{{ $loop->index }}" class="md:w-96 text-left">
                                <div class="space-y-6">
                                    <div class="flex gap-4">
                                        <div class="bg-orange-100 text-orange-600 p-3 rounded-2xl h-fit">
                                            <flux:icon.exclamation-triangle />
                                        </div>
                                        <div>
                                            <flux:heading size="lg">¿Confirmar Restauración?</flux:heading>
                                            <flux:subheading>
                                                Se sobreescribirán todos los datos actuales con la versión del 
                                                <span class="text-zinc-900 dark:text-white font-bold">{{ $backup['date'] }}</span>.
                                            </flux:subheading>
                                        </div>
                                    </div>

                                    <div class="p-4 bg-orange-50 dark:bg-orange-900/10 border border-orange-100 dark:border-orange-800 rounded-2xl">
                                        <flux:text size="xs" class="text-orange-700 dark:text-orange-400">
                                            <strong>Nota:</strong> Esta acción es irreversible. Se recomienda crear un punto de restauración actual antes de proceder.
                                        </flux:text>
                                    </div>

                                    <div class="flex gap-2">
                                        <flux:modal.close><flux:button variant="ghost" class="flex-1">Cancelar</flux:button></flux:modal.close>
                                        <flux:button variant="danger" class="flex-1" wire:click="restoreFromDisk('{{ $backup['name'] }}')" @click="Flux.modal('confirm-restore-{{ $loop->index }}').close()">Sí, restaurar datos</flux:button>
                                    </div>
                                </div>
                            </flux:modal>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4" class="text-center py-20 text-zinc-400">
                            <div class="flex flex-col items-center gap-2">
                                <flux:icon.cloud class="opacity-20" size="xl" />
                                <flux:text italic>No se encontraron copias de seguridad en el sistema.</flux:text>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>