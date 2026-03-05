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

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:sidebar.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full" data-test="logout-button">
                    {{ __('Log Out') }}
                </flux:sidebar.item>
            </form>
        </flux:sidebar>

        {{ $slot }}

        <div 
            x-data="{ show: false, message: '', variant: 'success' }" 
            x-on:notify.window="show = true; message = $event.detail.message; variant = $event.detail.variant || 'success'; setTimeout(() => show = false, 3500)"
            x-show="show"
            x-transition
            {{-- 'right-8' lo aleja del borde derecho y 'z-[100]' lo pone al frente de todo --}}
            class="fixed bottom-8 right-8 z-[100] p-4 rounded-xl shadow-2xl border text-white min-w-[280px]"
            :class="{
                'bg-red-600 border-red-500': variant === 'danger',
                'bg-yellow-500 border-yellow-400': variant === 'warning',
                'bg-zinc-900 border-zinc-700': variant === 'success'
            }"
            style="display: none;"
        >
            <div class="flex items-center gap-3">
                {{-- Icono dinámico según el tipo de mensaje (RNF-03) --}}
                <template x-if="variant === 'danger'"><span>⚠️</span></template>
                <template x-if="variant === 'success'"><span>✅</span></template>
                <span class="font-medium" x-text="message"></span>
            </div>
        </div>

        @fluxScripts
    </body>
</html>