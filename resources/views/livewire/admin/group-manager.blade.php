<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="flex justify-between items-center">
        <flux:heading level="1" size="lg">Grupos de Medicamentos</flux:heading>

        <div class="flex items-center gap-4">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar grupo..." class="w-64" />
            <flux:button icon="plus" wire:click="createGroup" variant="primary">Nuevo Grupo</flux:button>
        </div>
    </div>

    <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700">
        <x-table>
            <x-table.head>
                <x-table.heading>Nombre</x-table.heading>
                <x-table.heading>Descripción</x-table.heading>
                <x-table.heading class="text-right">Acciones</x-table.heading>
            </x-table.head>
            <x-table.body>
                @forelse($groups as $group)
                    <x-table.row>
                        <x-table.cell>
                            <flux:text class="font-medium">{{ $group->name }}</flux:text>
                        </x-table.cell>
                        <x-table.cell>
                            <flux:text class="text-sm text-gray-600 dark:text-gray-400 truncate max-w-sm">{{ $group->description ?: 'Sin descripción' }}</flux:text>
                        </x-table.cell>
                        <x-table.cell class="text-right">
                            <div class="flex justify-end gap-2">
                                <flux:button size="sm" icon="pencil-square" variant="ghost" wire:click="editGroup({{ $group->id }})" />
                                <flux:button size="sm" icon="trash" variant="danger" ghost wire:click="confirmDeactivate({{ $group->id }})" />
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="3" class="text-center">
                            <flux:text class="text-gray-500 dark:text-gray-400">No se encontraron grupos.</flux:text>
                        </x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table.body>
        </x-table>
    </div>

    <div class="mt-4">
        {{ $groups->links() }}
    </div>

    <!-- Modals -->
    <flux:modal name="group-form" class="min-w-lg">
        <form wire:submit="saveGroup" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingGroup ? 'Editar Grupo' : 'Registrar Nuevo Grupo' }}</flux:heading>
                <flux:subheading>Define la categoría para agrupar medicamentos asociados.</flux:subheading>
            </div>

            <div class="space-y-4">
                <flux:input wire:model="groupContext.name" label="Nombre del grupo" required />
                <flux:textarea wire:model="groupContext.description" label="Descripción (Opcional)" />
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Guardar Grupo</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="confirm-deactivation-group" class="min-w-xs">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg" class="text-left text-red-600 dark:text-red-400">¿Desactivar grupo?</flux:heading>
            </div>

            <flux:text class="text-left whitespace-normal">
                Esta acción marcará a <strong>{{ $editingGroup?->name }}</strong> como inactivo y no podrá ser asignado a nuevos medicamentos, pero se preservará su historial.
            </flux:text>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button wire:click="deactivateGroup" variant="danger">Desactivar</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
