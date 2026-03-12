<div class="space-y-6">
    {{-- Encabezado de la Sección --}}
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">Configuración de Descuentos y Recargos</flux:heading>
            <flux:subheading>Define las reglas comerciales que se aplicarán automáticamente en el Punto de Venta.</flux:subheading>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="create">Nueva Regla</flux:button>
    </header>

    <flux:card>
        <div class="space-y-4">
            {{-- Buscador Reactivo --}}
            <div class="flex gap-4">
                <flux:input 
                    wire:model.live.debounce.300ms="search" 
                    icon="magnifying-glass" 
                    placeholder="Buscar por nombre de regla..." 
                    class="flex-1"
                />
            </div>

            {{-- Tabla de Reglas --}}
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Nombre de la Regla</flux:table.column>
                    <flux:table.column>Tipo de Ajuste</flux:table.column>
                    <flux:table.column>Valor (%)</flux:table.column>
                    <flux:table.column>Estado</flux:table.column>
                    <flux:table.column align="right">Acciones</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($promotions as $promo)
                        <flux:table.row :key="$promo->id">
                            <flux:table.cell class="font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $promo->name }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge :color="$promo->type === 'discount' ? 'green' : 'orange'" variant="subtle">
                                    {{ $promo->type === 'discount' ? 'Descuento' : 'Recargo' }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell class="font-mono font-bold text-indigo-600">
                                {{ $promo->type === 'discount' ? '-' : '+' }}{{ number_format($promo->value, 0) }}%
                            </flux:table.cell>

                            <flux:table.cell>
                                <button wire:click="toggleStatus({{ $promo->id }})" class="focus:outline-none">
                                    <flux:badge :color="$promo->status ? 'green' : 'zinc'" size="sm" class="cursor-pointer hover:opacity-80 transition-opacity">
                                        {{ $promo->status ? 'Activo' : 'Inactivo' }}
                                    </flux:badge>
                                </button>
                            </flux:table.cell>

                            <flux:table.cell align="right">
                                <div class="flex gap-2 justify-end">
                                    <flux:button size="xs" icon="pencil-square" variant="ghost" wire:click="edit({{ $promo->id }})" tooltip="Editar regla" />
                                    
                                    <flux:modal.trigger name="delete-promotion-{{ $promo->id }}">
                                        <flux:button size="xs" icon="trash" variant="ghost" class="text-red-500 hover:text-red-700" tooltip="Eliminar" />
                                    </flux:modal.trigger>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>

                        {{-- Modal de Confirmación de Eliminación Individual --}}
                        <flux:modal name="delete-promotion-{{ $promo->id }}" class="md:w-96">
                            <div class="space-y-6">
                                <div>
                                    <flux:heading size="lg">¿Eliminar esta regla?</flux:heading>
                                    <flux:subheading>Esta acción no se puede deshacer. Las ventas pasadas que aplicaron este descuento no se verán afectadas.</flux:subheading>
                                </div>
                                <div class="flex gap-2 justify-end">
                                    <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                                    <flux:button variant="danger" wire:click="delete({{ $promo->id }})">Confirmar Eliminación</flux:button>
                                </div>
                            </div>
                        </flux:modal>

                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="text-center py-12 text-zinc-400 italic">
                                <div class="flex flex-col items-center">
                                    <flux:icon.receipt-percent class="w-8 h-8 mb-2 opacity-20" />
                                    <span>No se encontraron reglas de descuento configuradas.</span>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            {{-- Paginación --}}
            <div class="mt-4">
                {{ $promotions->links() }}
            </div>
        </div>
    </flux:card>

    {{-- MODAL DE CREACIÓN / EDICIÓN --}}
    <flux:modal name="promotion-modal" class="md:w-96">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $promotionId ? 'Editar Regla de Ajuste' : 'Nueva Regla de Ajuste' }}</flux:heading>
                <flux:subheading>Define el nombre público y el porcentaje que afectará al total de la venta.</flux:subheading>
            </div>

            <flux:input 
                wire:model="name" 
                label="Nombre de la Regla" 
                placeholder="Ej: Obra Social 40% o Recargo Tarjeta" 
                required 
            />
            
            <div class="grid grid-cols-2 gap-4">
                <flux:input 
                    wire:model="value" 
                    type="number" 
                    label="Porcentaje (%)" 
                    placeholder="0" 
                    min="0" 
                    max="100" 
                    step="0.01"
                    required 
                />
                
                <flux:select wire:model="type" label="Efecto">
                    <option value="discount">Descuento (-)</option>
                    <option value="surcharge">Recargo (+)</option>
                </flux:select>
            </div>

            <flux:field>
                <flux:label>Disponibilidad</flux:label>
                <flux:description>Si la desactivas, dejará de aparecer como opción en el Punto de Venta.</flux:description>
                <div class="mt-3">
                    <flux:switch wire:model="status" label="Habilitar regla inmediatamente" />
                </div>
            </flux:field>

            <div class="flex justify-end gap-2 pt-4">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">
                    {{ $promotionId ? 'Guardar Cambios' : 'Crear Regla' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>