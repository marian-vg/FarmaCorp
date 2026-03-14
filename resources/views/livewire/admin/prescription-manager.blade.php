<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex justify-between items-center">
        <div>
            <flux:heading level="1" size="lg">Archivo de Recetas Digitales</flux:heading>
            <flux:subheading>Historial de prescripciones médicas vinculadas a ventas.</flux:subheading>
        </div>
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar por cliente..." class="w-64" />
    </div>

    <x-table>
        <x-table.head>
            <x-table.heading>Fecha</x-table.heading>
            <x-table.heading>Cliente</x-table.heading>
            <x-table.heading>Comprobante</x-table.heading>
            <x-table.heading>Medicamentos</x-table.heading>
            <x-table.heading class="text-right">Acciones</x-table.heading>
        </x-table.head>
        <x-table.body>
            @forelse($prescriptions as $p)
                <x-table.row>
                    <x-table.cell class="text-xs font-mono">{{ $p->created_at->format('d/m/Y H:i') }}</x-table.cell>
                    <x-table.cell>
                        <div class="flex flex-col">
                            <span class="font-bold text-zinc-900 dark:text-white">{{ $p->cliente->first_name }} {{ $p->cliente->last_name }}</span>
                            <span class="text-xs text-zinc-500">DNI: {{ $p->cliente->dni }}</span>
                        </div>
                    </x-table.cell>
                    <x-table.cell>
                        <flux:badge variant="subtle" color="zinc">#{{ str_pad($p->factura_id, 6, '0', STR_PAD_LEFT) }}</flux:badge>
                    </x-table.cell>
                    <x-table.cell>
                        <div class="max-w-xs truncate text-xs text-zinc-600">
                            {{-- Aquí podrías listar los medicamentos de la factura que pedían receta --}}
                            {{ $p->factura->details->pluck('product.name')->implode(', ') }}
                        </div>
                    </x-table.cell>
                    <x-table.cell class="text-right">
                        <div class="flex justify-end gap-2">
                            <flux:button size="xs" icon="eye" variant="ghost" tooltip="Ver Factura" href="{{ route('admin.sales', ['search' => $p->factura_id]) }}" />
                            <flux:button size="xs" icon="document-arrow-down" variant="subtle" color="indigo" wire:click="download({{ $p->id }})" tooltip="Descargar PDF" />
                        </div>
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.row>
                    <x-table.cell colspan="5" class="text-center py-20">
                        <flux:icon.document-duplicate class="mx-auto mb-4 opacity-20 w-12 h-12" />
                        <flux:text italic>No se han cargado recetas recientemente.</flux:text>
                    </x-table.cell>
                </x-table.row>
            @endforelse
        </x-table.body>
    </x-table>
    
    {{ $prescriptions->links() }}
</div>