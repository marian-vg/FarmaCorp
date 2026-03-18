<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-900">
        <div class="flex min-h-screen flex-col items-center justify-center p-6">
            <flux:card class="flex w-full max-w-md flex-col items-center justify-center p-8 text-center shadow-xl">
                <flux:icon name="clock" class="mb-6 h-16 w-16 text-orange-500" />
                
                <flux:heading size="xl" class="mb-2">Página Expirada</flux:heading>
                
                <flux:subheading class="mb-8 max-w-sm">
                    Por motivos de seguridad, tu sesión ha expirado tras un periodo de inactividad o tu petición no pudo ser validada de forma segura.
                </flux:subheading>

                <div class="flex flex-col gap-3 w-full">
                    <flux:button variant="primary" x-on:click="window.location.reload()" class="w-full">
                        Refrescar Página
                    </flux:button>

                    <flux:button variant="ghost" href="{{ route('login') }}" class="w-full">
                        Ir al Inicio / Login
                    </flux:button>
                </div>
            </flux:card>
        </div>

        @fluxScripts
    </body>
</html>