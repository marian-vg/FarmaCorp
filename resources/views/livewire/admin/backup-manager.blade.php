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
                    @can('admin-backup.crear')
                    <flux:select wire:model="destination" label="Destino de la Copia">
                        <option value="local">Solo Servidor Local</option>
                        <option value="email">Enviar al Correo</option>
                        <option value="supabase">Nube (Supabase Storage)</option>
                        <option value="all">Correo + Nube (Máxima Seguridad)</option>
                    </flux:select>

                    <flux:button wire:click="createInternalBackup" icon="cloud-arrow-up" variant="primary">
                        Ejecutar Backup
                    </flux:button>
                    @endcan
                </div>
            </div>
        </div>

        <x-table>
            <x-table.head>
                <x-table.heading>Fecha y Hora</x-table.heading>
                <x-table.heading>Identificador</x-table.heading>
                <x-table.heading>Peso</x-table.heading>
                <x-table.heading class="text-right">Gestión</x-table.heading>
            </x-table.head>

            <x-table.body>
                @forelse($backups as $backup)
                    <x-table.row :key="$backup['name']">
                        <x-table.cell class="font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $backup['date'] }}
                        </x-table.cell>
                        
                        <x-table.cell>
                            <flux:badge size="sm" color="zinc" variant="outline" class="font-mono text-[10px]">
                                {{ $backup['name'] }}
                            </flux:badge>
                        </x-table.cell>

                        <x-table.cell class="text-zinc-500 text-xs italic">
                            {{ $backup['size'] }}
                        </x-table.cell>

                        <x-table.cell class="text-right">
                            <div class="flex gap-2 justify-end">
                                @can('admin-backup.restaurar')
                                <flux:modal.trigger name="confirm-restore-{{ $loop->index }}">
                                    <flux:button size="xs" icon="arrow-path" variant="subtle" color="orange" />
                                </flux:modal.trigger>
                                @endcan
                                
                                @can('admin-backup.eliminar')
                                <flux:button size="xs" icon="trash" variant="ghost" color="red" wire:click="deleteBackup('{{ $backup['name'] }}')" />
                                @endcan
                            </div>

                            @can('admin-backup.restaurar')
                            <flux:modal name="confirm-restore-{{ $loop->index }}" class="md:w-96 text-left">
                                <div class="space-y-6">
                                    <div class="flex gap-4">
                                        <div class="bg-orange-100 text-orange-600 p-3 rounded-2xl h-fit shrink-0">
                                            <flux:icon.exclamation-triangle />
                                        </div>
                                        <div class="flex-1 min-w-0 whitespace-normal break-words">
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
                            @endcan
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="4" class="text-center py-20 text-zinc-400">
                            <flux:icon.cloud class="opacity-20 mx-auto mb-2" size="xl" />
                            <flux:text italic>No hay copias de seguridad.</flux:text>
                        </x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table.body>
        </x-table>
    </flux:card>
</div>