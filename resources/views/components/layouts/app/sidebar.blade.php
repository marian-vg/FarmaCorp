<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen flex bg-white dark:bg-zinc-800">

        <flux:sidebar sticky collapsible class="shadow-lg shadow-farmacorp-shadow/70">
            <flux:sidebar.toggle class="flex items-center" size="sm" icon="bars-3" />

            {{-- VISTA ADMINISTRADOR --}}
            @can('admin-panel.acceder')
                <flux:sidebar.item icon="home" href="{{ route('admin.dashboard') }}" :current="request()->routeIs('admin.dashboard')">Dashboard</flux:sidebar.item>
            @endcan
            {{-- CATEGORÍAS SEGÚN PERMISOS --}}
            @can('inventario.acceder')
                <flux:sidebar.item icon="beaker" href="{{ route('admin.products') }}" :current="request()->routeIs('admin.products')">Catálogo - Productos</flux:sidebar.item>
                <flux:sidebar.item icon="folder" href="{{ route('admin.groups') }}" :current="request()->routeIs('admin.groups')">Catálogo - Grupos</flux:sidebar.item>
                <flux:sidebar.item icon="clipboard-document-list" href="{{ route('admin.medicines') }}" :current="request()->routeIs('admin.medicines')">Alta de Medicamento</flux:sidebar.item>
            @endcan

            @can('stock.ingreso')
                <flux:sidebar.item icon="arrow-down-tray" href="{{ route('admin.stock.ingresos') }}" :current="request()->routeIs('admin.stock.ingresos')">Stock - Ingreso Físico</flux:sidebar.item>
            @endcan
            @can('stock.egreso')
                <flux:sidebar.item icon="arrow-top-right-on-square" href="{{ route('admin.stock.egresos') }}" :current="request()->routeIs('admin.stock.egresos')">Stock - Egresos/Ajustes</flux:sidebar.item>
            @endcan
            @can('stock.acceder')
                <flux:sidebar.item icon="clock" href="{{ route('admin.stock.historial') }}" :current="request()->routeIs('admin.stock.historial')">Stock - Kardex</flux:sidebar.item>
            @endcan

            @can('roles.acceder')
                <flux:sidebar.item icon="users" href="{{ route('admin.profiles') }}" :current="request()->routeIs('admin.profiles')">Perfiles y Accesos</flux:sidebar.item>
            @endcan

            @can('clientes.acceder')
                <flux:sidebar.item icon="user-group" href="{{ route('admin.clients') }}" :current="request()->routeIs(['admin.clients', 'clients.index'])">Clientes</flux:sidebar.item>
                <flux:sidebar.item icon="credit-card" href="{{ route('admin.debts') }}" :current="request()->routeIs('admin.debts')">Cuentas Corrientes</flux:sidebar.item>
            @endcan

            @can('admin-ventas.acceder')
                <flux:sidebar.item icon="banknotes" href="{{ route('admin.sales') }}" :current="request()->routeIs('admin.sales')">Ventas</flux:sidebar.item>
                <flux:sidebar.item icon="document-text" href="{{ route('admin.prescriptions') }}" :current="request()->routeIs('admin.prescriptions')">Archivo de Recetas</flux:sidebar.item>
            @endcan

            @can('admin-promociones.acceder')
                <flux:sidebar.item icon="receipt-percent" href="{{ route('admin.promotions') }}" :current="request()->routeIs('admin.promotions')">Config. Descuentos</flux:sidebar.item>
            @endcan

            @can('facturacion.acceder')
                <flux:sidebar.item icon="shopping-cart" href="{{ route('ventas.pos') }}" :current="request()->routeIs('ventas.pos')">Punto de Venta</flux:sidebar.item>
            @endcan

            @can('caja.acceder')
                <flux:sidebar.item icon="wallet" href="{{ route('user.dashboard') }}" :current="request()->routeIs('user.dashboard')">Mi Caja Operativa</flux:sidebar.item>
            @endcan

            @can('admin-cajas.acceder')
                <flux:sidebar.item icon="archive-box" href="{{ route('admin.cajas') }}" :current="request()->routeIs('admin.cajas')">Administración de Cajas</flux:sidebar.item>
            @endcan

            <flux:spacer/>
            <flux:separator/>

            <flux:sidebar.item icon="cog-6-tooth" href="{{ route('settings.index') }}" :current="request()->routeIs('settings.index')">Configuración</flux:sidebar.item>
            <flux:sidebar.item icon="book-open" href="{{ route('manual') }}" :current="request()->routeIs('manual')">Documentación</flux:sidebar.item>

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

        <x-toast/>
        @fluxScripts
    </body>
</html>