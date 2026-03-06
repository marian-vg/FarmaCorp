<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen flex bg-white dark:bg-zinc-800">

        <flux:sidebar sticky collapsible class="shadow-lg shadow-farmacorp-shadow/70">
            <flux:sidebar.toggle class="flex items-center" size="sm" icon="bars-3" />

            {{-- VISTA ADMINISTRADOR --}}
            @hasrole('admin')
                <flux:sidebar.item icon="home" href="{{ route('admin.dashboard') }}" :current="request()->routeIs('admin.dashboard')">Dashboard</flux:sidebar.item>
                <flux:sidebar.item icon="beaker" href="{{ route('admin.products') }}" :current="request()->routeIs('admin.products')">Catálogo - Productos</flux:sidebar.item>
                <flux:sidebar.item icon="folder" href="{{ route('admin.groups') }}" :current="request()->routeIs('admin.groups')">Catálogo - Grupos</flux:sidebar.item>
                <flux:sidebar.item icon="clipboard-document-list" href="{{ route('admin.medicines') }}" :current="request()->routeIs('admin.medicines')">Alta de Medicamento</flux:sidebar.item>
                <flux:sidebar.item icon="arrow-down-tray" href="{{ route('admin.stock.ingresos') }}" :current="request()->routeIs('admin.stock.ingresos')">Stock - Ingreso Físico</flux:sidebar.item>
                <flux:sidebar.item icon="arrow-top-right-on-square" href="{{ route('admin.stock.egresos') }}" :current="request()->routeIs('admin.stock.egresos')">Stock - Egresos/Ajustes</flux:sidebar.item>
                <flux:sidebar.item icon="clock" href="{{ route('admin.stock.historial') }}" :current="request()->routeIs('admin.stock.historial')">Stock - Kardex</flux:sidebar.item>
                <flux:sidebar.item icon="users" href="{{ route('admin.profiles') }}" :current="request()->routeIs('admin.profiles')">Perfiles y Accesos</flux:sidebar.item>
                <flux:sidebar.item icon="user-group" href="{{ route('admin.clients') }}" :current="request()->routeIs('admin.clients')">Clientes</flux:sidebar.item>
                <flux:sidebar.item icon="banknotes" href="{{ route('admin.sales') }}" :current="request()->routeIs('admin.sales')">Ventas</flux:sidebar.item>
                <flux:sidebar.item icon="archive-box" href="{{ route('admin.cajas') }}" :current="request()->routeIs('admin.cajas')">Caja</flux:sidebar.item>
            @endhasrole

            {{-- VISTA EMPLEADO --}}
            @hasrole('empleado')
                <flux:sidebar.item icon="shopping-cart" href="{{ route('ventas.pos') }}" :current="request()->routeIs('ventas.pos')">Vender (POS)</flux:sidebar.item>
                <flux:sidebar.item icon="wallet" href="{{ route('dashboard') }}" :current="request()->routeIs('dashboard')">Mi Caja Operativa</flux:sidebar.item>
            @endhasrole

            <flux:spacer/>

            <flux:sidebar.item icon="cog-6-tooth" href="{{ route('settings.index') }}" :current="request()->routeIs('settings.index')">Configuración</flux:sidebar.item>

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:sidebar.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full" data-test="logout-button">
                    {{ __('Log Out') }}
                </flux:sidebar.item>
            </form>
        </flux:sidebar>

        <main class="flex-1 min-h-screen bg-white dark:bg-zinc-900">
            {{ $slot }}
        </main>       

        @fluxScripts
    </body>
</html>