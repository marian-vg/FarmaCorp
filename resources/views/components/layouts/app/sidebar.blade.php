<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="flex items-center" size="sm" icon="bars-3" />

            {{-- VISTA ADMINISTRADOR --}}
            @hasrole('admin')
                <flux:sidebar.item icon="home" href="{{ route('admin.dashboard') }}" :current="request()->routeIs('admin.dashboard')">Dashboard</flux:sidebar.item>
                <flux:sidebar.item icon="users" href="{{ route('admin.profiles') }}" :current="request()->routeIs('admin.profiles')">Perfiles y Accesos</flux:sidebar.item>
                <flux:sidebar.item icon="shield-check" href="{{ route('admin.permissions') }}" :current="request()->routeIs('admin.permissions')">Permisos del Sistema</flux:sidebar.item>
                <flux:sidebar.item icon="user-group" href="{{ route('admin.clients') }}" :current="request()->routeIs('admin.clients')">Módulo de Clientes</flux:sidebar.item>
                
                {{-- NUEVA SECCIÓN: Módulo de Productos (RF-01, RF-02, RF-03) --}}
                <flux:sidebar.item icon="shopping-bag" href="{{ route('admin.products') }}" :current="request()->routeIs('admin.products')">Módulo de Productos</flux:sidebar.item>
                
                <flux:sidebar.item icon="archive-box" href="{{ route('admin.cajas') }}" :current="request()->routeIs('admin.cajas')">Módulo de Caja</flux:sidebar.item>
            @endhasrole

            {{-- VISTA EMPLEADO --}}
            @hasrole('empleado')
                <flux:sidebar.item icon="wallet" href="{{ route('dashboard') }}" :current="request()->routeIs('dashboard')">Mi Caja Operativa</flux:sidebar.item>
            @endhasrole

            <flux:spacer/>

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:sidebar.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full" data-test="logout-button">
                    {{ __('Log Out') }}
                </flux:sidebar.item>
            </form>
        </flux:sidebar>

        {{ $slot }}

        @fluxScripts
    </body>
</html>