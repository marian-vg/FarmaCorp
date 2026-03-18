<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="flex justify-between items-center">
        <flux:heading level="1" size="lg">Administración de Obras Sociales</flux:heading>

        <div class="flex items-center gap-4">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar Obra Social..." class="w-64" />
            <flux:button icon="plus" wire:click="createOS" variant="primary">Nueva Obra Social</flux:button>
        </div>
    </div>

    {{-- TABLA PRINCIPAL --}}
    <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700">
        <x-table>
            <x-table.head>
                <x-table.heading>Nombre</x-table.heading>
                <x-table.heading class="text-center">Estado</x-table.heading>
                <x-table.heading class="text-right">Acciones</x-table.heading>
            </x-table.head>
            <x-table.body>
                @forelse($obrasSociales as $os)
                    <x-table.row>
                        <x-table.cell><flux:text class="font-medium">{{ $os->name }}</flux:text></x-table.cell>
                        <x-table.cell class="text-center">
                            <flux:badge :color="$os->is_active ? 'green' : 'red'" size="sm">{{ $os->is_active ? 'Activa' : 'Inactiva' }}</flux:badge>
                        </x-table.cell>
                        <x-table.cell class="text-right">
                            <div class="flex justify-end gap-2">
                                <flux:button size="sm" icon="eye" variant="ghost" wire:click="manageVademecum({{ $os->id }})" tooltip="Gestionar Vademécum" />
                                <flux:button size="sm" icon="pencil-square" variant="ghost" wire:click="editOS({{ $os->id }})" />
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row><x-table.cell colspan="3" class="text-center py-4">No hay registros.</x-table.cell></x-table.row>
                @endforelse
            </x-table.body>
        </x-table>
    </div>
    <div class="mt-4">{{ $obrasSociales->links() }}</div>

    {{-- MODAL VADEMÉCUM (EL OJO) --}}
    <flux:modal name="vademecum-modal" class="min-w-[50rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Vademécum: {{ $selectedOS?->name }}</flux:heading>
                <flux:subheading>Selecciona medicamentos y define el porcentaje de cobertura.</flux:subheading>
            </div>

            {{-- Filtros dentro del modal --}}
            <div class="flex gap-4 items-end bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-xl border border-zinc-200">
                <flux:input wire:model.live.debounce.300ms="searchProduct" label="Medicamento" placeholder="Buscar..." class="flex-1" />
                <flux:select wire:model.live="filterGroup" label="Categoría" class="w-48">
                    <option value="">Todas</option>
                    @foreach($groups as $g) <option value="{{ $g->id }}">{{ $g->name }}</option> @endforeach
                </flux:select>
                <div class="w-32">
                    <flux:input type="number" wire:model="bulkDiscount" label="% Descuento" />
                </div>
                <flux:button variant="primary" icon="check" wire:click="applyBulkDiscount">Aplicar</flux:button>
            </div>

            {{-- Lista de Medicamentos con Checkbox --}}
            <div class="max-h-96 overflow-y-auto border rounded-lg">
                <x-table>
                    <x-table.head>
                        <x-table.heading class="w-10"></x-table.heading>
                        <x-table.heading>Medicamento / Presentación</x-table.heading>
                        <x-table.heading>Grupo</x-table.heading>
                        <x-table.heading class="text-right">Cobertura Actual</x-table.heading>
                    </x-table.head>
                    <x-table.body>
                        @foreach($medicinesList as $med)
                            @php
                                $coberturaActual = $selectedOS ? DB::table('obra_social_medicine')
                                    ->where('obra_social_id', $selectedOS->id)
                                    ->where('medicine_id', $med->id)
                                    ->value('discount_percentage') : 0;
                            @endphp
                            <x-table.row>
                                <x-table.cell>
                                    <input type="checkbox" wire:model="selectedMedicines" value="{{ $med->id }}" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                </x-table.cell>
                                <x-table.cell>
                                    <flux:text size="sm" class="font-medium">{{ $med->presentation_name ?: $med->product->name }}</flux:text>
                                </x-table.cell>
                                <x-table.cell><flux:text size="xs">{{ $med->group->name }}</flux:text></x-table.cell>
                                <x-table.cell class="text-right">
                                    @if($coberturaActual != 0)
                                        <flux:badge color="green" size="sm" variant="subtle">{{ (float)$coberturaActual }}%</flux:badge>
                                    @else
                                        <flux:text size="xs" class="text-zinc-400">0%</flux:text>
                                    @endif
                                </x-table.cell>
                            </x-table.row>
                        @endforeach
                    </x-table.body>
                </x-table>
            </div>

            <div class="flex justify-end pt-4"><flux:modal.close><flux:button>Cerrar</flux:button></flux:modal.close></div>
        </div>
    </flux:modal>

    {{-- MODAL CRUD (NUEVO/EDITAR) --}}
    <flux:modal name="os-form" class="min-w-96">
        <form wire:submit="saveOS" class="space-y-6">
            <flux:heading size="lg">{{ $editingOS ? 'Editar Obra Social' : 'Nueva Obra Social' }}</flux:heading>
            <flux:input wire:model="osContext.name" label="Nombre de la Institución" required />
            <flux:switch wire:model="osContext.is_active" label="Estado Activo" />
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">Guardar</flux:button>
            </div>
        </form>
    </flux:modal>
</div>