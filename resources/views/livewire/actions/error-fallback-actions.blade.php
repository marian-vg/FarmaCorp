<div>
    <flux:subheading class="mb-8 max-w-sm text-center">
        @if ($fallbackRoute)
            No tienes el nivel de autorización requerido para acceder a esta página específica.
        @else
            Tu cuenta ha sido creada, pero actualmente no tienes ningún módulo o permiso asignado para operar en el sistema.
        @endif
    </flux:subheading>

    <div class="flex flex-col gap-3 w-full">
        @if ($fallbackRoute)
            <flux:button variant="primary" href="{{ $fallbackRoute }}" class="w-full">
                Volver a {{ $fallbackName }}
            </flux:button>
        @endif

        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <flux:button type="submit" variant="{{ $fallbackRoute ? 'ghost' : 'primary' }}" class="w-full">
                Cerrar Sesión / Volver al Login
            </flux:button>
        </form>
    </div>
</div>
