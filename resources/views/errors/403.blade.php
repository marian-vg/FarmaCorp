<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-900">
        <div class="flex min-h-screen flex-col items-center justify-center p-6">
            <flux:card class="flex w-full max-w-md flex-col items-center justify-center p-8 text-center shadow-xl">
                <flux:icon name="shield-exclamation" class="mb-6 h-16 w-16 text-red-500" />
                
                <flux:heading size="xl" class="mb-2">Acceso Denegado</flux:heading>
                
                @livewire('actions.error-fallback-actions')
            </flux:card>
        </div>

        @fluxScripts
    </body>
</html>