<div class="space-y-6">
    <header class="flex justify-between items-center">
        <div>
            <flux:heading size="xl">Puntos de Restauración</flux:heading>
            <flux:subheading>Administra copias de seguridad internas sin descargar archivos.</flux:subheading>
        </div>
        <flux:button wire:click="createInternalBackup" icon="plus" variant="primary">
            Crear Nuevo Punto
        </flux:button>
    </header>

    <flux:card>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Fecha de Creación</flux:table.column>
                <flux:table.column>Nombre del Archivo</flux:table.column>
                <flux:table.column>Tamaño</flux:table.column>
                <flux:table.column align="right">Acciones</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($backups as $backup)
                    <flux:table.row :key="$backup['name']">
                        <flux:table.cell>{{ $backup['date'] }}</flux:table.cell>
                        <flux:table.cell class="font-mono text-xs">{{ $backup['name'] }}</flux:table.cell>
                        <flux:table.cell>{{ $backup['size'] }}</flux:table.cell>
                        <flux:table.cell align="right">
                            <div class="flex gap-2 justify-end">
                                {{-- CAMBIO AQUÍ: Agregamos el $ a loop --}}
                                <flux:modal.trigger name="confirm-restore-{{ $loop->index }}">
                                    <flux:button size="xs" icon="arrow-path" variant="ghost" class="text-orange-600">Restaurar</flux:button>
                                </flux:modal.trigger>

                                <flux:button size="xs" icon="trash" variant="ghost" class="text-red-500" wire:click="deleteBackup('{{ $backup['name'] }}')" />
                            </div>

                            {{-- CAMBIO AQUÍ: Agregamos el $ a loop --}}
                            <flux:modal name="confirm-restore-{{ $loop->index }}" class="md:w-96 text-left">
                                <div class="space-y-6">
                                    <div>
                                        <flux:heading size="lg">¿Restaurar sistema?</flux:heading>
                                        <flux:subheading>Se perderán todos los datos cargados después del <strong>{{ $backup['date'] }}</strong>. Esta acción no se puede deshacer.</flux:subheading>
                                    </div>
                                    <div class="flex gap-2">
                                        <flux:modal.close><flux:button variant="ghost" class="flex-1">Cancelar</flux:button></flux:modal.close>
                                        <flux:button variant="danger" class="flex-1" wire:click="restoreFromDisk('{{ $backup['name'] }}')" @click="Flux.modal('confirm-restore-{{ $loop->index }}').close()">Confirmar</flux:button>
                                    </div>
                                </div>
                            </flux:modal>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4" class="text-center py-10 text-zinc-400 italic">No hay puntos de restauración guardados en el servidor.</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>