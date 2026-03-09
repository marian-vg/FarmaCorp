<div class="px-4 py-8 max-w-7xl mx-auto space-y-6">
    <div class="mb-4">
        <flux:heading level="1" size="xl">Configuración del Sistema</flux:heading>
        <flux:subheading>Gestiona tus preferencias personales y de entorno de trabajo.</flux:subheading>
    </div>

    <flux:card>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <flux:heading>Apariencia</flux:heading>
                <flux:subheading>Personaliza el comportamiento visual del sistema.</flux:subheading>
            </div>
            
            <div x-data="{ 
                get isDark() { return $flux.dark },
                set isDark(val) { $flux.appearance = val ? 'dark' : 'light' }
            }">
                <flux:switch x-model="isDark" label="Modo Oscuro" />
            </div>
        </div>
    </flux:card>
</div>
