<div class="px-4 py-8 max-w-7xl mx-auto space-y-10">
    <div class="flex flex-col gap-1">
        <flux:heading level="1" size="xl">Configuración del Sistema</flux:heading>
        <flux:subheading>Gestiona las preferencias del entorno y la integridad de los datos maestros.</flux:subheading>
    </div>

    <section class="space-y-4 mb-10">
        <div class="flex items-center gap-2 text-zinc-500">
            <flux:icon.swatch variant="micro" />
            <flux:heading size="sm" class="uppercase tracking-widest font-bold">Preferencia Visual</flux:heading>
        </div>
        
        <flux:card>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                <div class="flex gap-4">
                    <div class="bg-indigo-100 dark:bg-indigo-900/30 p-3 rounded-2xl text-indigo-600 size-10">
                        <flux:icon.moon variant="micro" x-show="$flux.dark" />
                        <flux:icon.sun variant="micro" x-show="!$flux.dark" />
                    </div>
                    <div>
                        <flux:heading>Modo de Visualización</flux:heading>
                        <flux:subheading>Alterna entre el tema claro y oscuro para tu comodidad visual.</flux:subheading>
                    </div>
                </div>
                
                <div x-data="{ 
                    get isDark() { return $flux.dark },
                    set isDark(val) { $flux.appearance = val ? 'dark' : 'light' }
                }" class="flex items-center gap-3 bg-zinc-50 dark:bg-zinc-800 p-2 rounded-full border border-zinc-200 dark:border-zinc-700">
                    <flux:switch x-model="isDark" />
                </div>
            </div>
        </flux:card>
    </section>

    <flux:separator variant="subtle" />

    <section class="space-y-4">
        <div class="flex items-center gap-2 text-zinc-500">
            <flux:icon.shield-check variant="micro" />
            <flux:heading size="sm" class="uppercase tracking-widest font-bold">Seguridad e Integridad</flux:heading>
        </div>

        @livewire('admin.backup-manager')
    </section>
</div>