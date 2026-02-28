<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="flex justify-between items-center">
        <flux:heading level="1" size="lg">Medicamentos Específicos</flux:heading>

        <div class="flex items-center gap-4">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar medicamento..." class="w-64" />
            <flux:button icon="plus" wire:click="createMedicine" variant="primary">Registrar Medicamento</flux:button>
        </div>
    </div>

    <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
            <thead class="bg-gray-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Producto Base</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Grupo Farmacológico</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Nivel / Dosis</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Vencimiento</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Estado / Riesgo</th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200 dark:bg-zinc-900 dark:divide-zinc-700">
                @forelse($medicines as $medicine)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <flux:text class="font-medium">{{ $medicine->product->name }}</flux:text>
                            <div class="text-xs text-gray-500">${{ number_format($medicine->product->price, 2) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <flux:text>{{ $medicine->group->name }}</flux:text>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <flux:text>{{ $medicine->level ?: 'N/A' }}</flux:text>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($medicine->expiration_date)
                                <flux:text class="{{ $medicine->expiration_date->isPast() ? 'text-red-500 font-bold' : '' }}">
                                    {{ $medicine->expiration_date->format('d/m/Y') }}
                                </flux:text>
                            @else
                                <flux:text class="text-gray-400">Sin definir</flux:text>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col items-start gap-1">
                                @if($medicine->product->status)
                                    <flux:badge variant="success" size="sm">Activo</flux:badge>
                                @else
                                    <flux:badge variant="danger" size="sm">Inactivo</flux:badge>
                                @endif
                                
                                @if($medicine->is_psychotropic)
                                    <flux:badge variant="danger" size="sm">Psicotrópico</flux:badge>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center">
                            <flux:text class="text-gray-500 dark:text-gray-400">No se encontraron medicamentos.</flux:text>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $medicines->links() }}
    </div>

    <!-- Create Modal (Alta de medicamento desde producto existente) -->
    <flux:modal name="medicine-form" class="min-w-[40rem]">
        <form wire:submit="saveMedicine" class="space-y-6">
            <div>
                <flux:heading size="lg">Especificación Farmacológica</flux:heading>
                <flux:subheading>Asigna propiedades médicas a un producto genérico existente.</flux:subheading>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <flux:select wire:model="context.product_id" label="Producto Base" placeholder="Seleccione un producto disponible" required>
                        @foreach($availableProducts as $product)
                            <flux:select.option value="{{ $product->id }}">{{ $product->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                
                <div class="col-span-2 sm:col-span-1">
                    <flux:select wire:model="context.group_id" label="Grupo Farmacológico" placeholder="Seleccione un grupo" required>
                        @foreach($groups as $group)
                            <flux:select.option value="{{ $group->id }}">{{ $group->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <div class="col-span-2 sm:col-span-1">
                    <flux:input wire:model="context.level" label="Nivel / Dosis (Opcional)" placeholder="Ej. 10mg, Jarabe infantil" />
                </div>

                <div class="col-span-2">
                    <flux:textarea wire:model="context.leaflet" label="Prospecto / Casos de uso (Opcional)" rows="3" />
                </div>

                <div class="col-span-2 sm:col-span-1">
                    <flux:input type="date" wire:model="context.expiration_date" label="Fecha de Vencimiento Lote Actual" />
                </div>

                <div class="col-span-2 sm:col-span-1 flex items-center mt-6">
                    <flux:switch wire:model="context.is_psychotropic" label="Es Psicotrópico (Control estricto)" />
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Guardar Medicamento</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
