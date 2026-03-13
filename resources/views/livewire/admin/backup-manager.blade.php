<div class="space-y-6">
    <flux:card class="!p-0 overflow-hidden">
        <div class="p-6 border-b border-zinc-100 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/20">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div>
                    <flux:heading size="lg">Resguardos del Sistema</flux:heading>
                    <flux:subheading>Protege la información fuera del servidor local.</flux:subheading>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-3 items-end">
                    {{-- FIX: Eliminado variant="subtle" para evitar error --}}
                    <flux:select wire:model="destination" label="Destino de la Copia">
                        <option value="local">Solo Servidor Local</option>
                        <option value="email">Enviar al Correo</option>
                        <option value="supabase">Nube (Supabase Storage)</option>
                        <option value="all">Correo + Nube (Máxima Seguridad)</option>
                    </flux:select>

                    <flux:button wire:click="createInternalBackup" icon="cloud-arrow-up" variant="primary">
                        Ejecutar Backup
                    </flux:button>
                </div>
            </div>
        </div>

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
                                    <flux:button size="xs" icon="arrow-path" variant="subtle" color="orange" />
                                </flux:modal.trigger>
                                <flux:button size="xs" icon="trash" variant="ghost" color="red" wire:click="deleteBackup('{{ $backup['name'] }}')" />
                            </div>

                            <flux:modal name="confirm-restore-{{ $loop->index }}" class="md:w-96 text-left">
                                <div class="space-y-6">
                                    <div class="flex gap-4">
                                        <div class="bg-orange-100 text-orange-600 p-3 rounded-2xl h-fit">
                                            <flux:icon.exclamation-triangle />
                                        </div>
                                        <div>
                                            <flux:heading size="lg">¿Confirmar Restauración?</flux:heading>
                                            <flux:subheading>Se sobreescribirán todos los datos con la versión del <strong>{{ $backup['date'] }}</strong>.</flux:subheading>
                                        </div>
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
                            <flux:icon.cloud class="opacity-20 mx-auto mb-2" size="xl" />
                            <flux:text italic>No hay copias de seguridad.</flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>
    {{-- Añade esto arriba o abajo de tu card actual de backups --}}
<flux:card class="space-y-6 mb-6">
    <div>
        <flux:heading size="lg">Automatización</flux:heading>
        <flux:subheading>Configura el sistema para que realice copias de seguridad sin intervención humana.</flux:subheading>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
        <flux:select wire:model="auto_frequency" label="Frecuencia">
            <option value="none">Desactivado</option>
            <option value="daily">Diario (Media noche)</option>
            <option value="weekly">Semanal (Domingos)</option>
            <option value="monthly">Mensual (Día 1)</option>
        </flux:select>

        <flux:select wire:model="auto_destination" label="Destino Automático">
            <option value="local">Solo Local</option>
            <option value="email">Enviar al Correo</option>
            <option value="supabase">Nube (Supabase)</option>
            <option value="all">Correo + Nube</option>
        </flux:select>

        <flux:button wire:click="saveSettings" variant="subtle" icon="check">Guardar Preferencias</flux:button>
    </div>
</flux:card>
</div>