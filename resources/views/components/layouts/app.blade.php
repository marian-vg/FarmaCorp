<x-layouts.app.sidebar :title="$title ?? null">
    <flux:main>
        {{ $slot }}
    </flux:main>

    {{-- EL ESLABÓN PERDIDO: Cargamos la librería de gráficos --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</x-layouts.app.sidebar>