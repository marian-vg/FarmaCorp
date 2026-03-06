<?php

use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\User\Dashboard as UserDashboard;
use App\Livewire\Admin\ProfileManager;
use App\Livewire\Admin\GroupManager;
use App\Livewire\Admin\ProductManager;
use App\Livewire\Admin\MedicineManager;
use App\Livewire\Clients\ClientManager;
use App\Livewire\Admin\CajaManager;
use App\Livewire\Admin\SalesManager;
use App\Livewire\User\VentaManager;
use App\Livewire\Actions\SettingsManager;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;
use App\Livewire\Admin\StockIngresoManager;
use App\Livewire\Admin\StockEgresoManager;
use App\Livewire\Admin\StockHistorialManager;

Route::get('/', function () {
    return view('livewire.auth.login');
})->name('principal-page')->middleware(['guest']);

// This will make the login and register routes available to guests and not for users that are already logged in
Route::middleware(['guest'])->group(function () {
    Route::get('login', function () {
        return view('livewire.auth.login');
    })->name('login');
    Route::get('register', function () {
        return view('livewire.auth.register');
    })->name('register');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        if (auth()->user()->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('user.dashboard');
    })->name('dashboard');

    Route::middleware(['role:admin'])->group(function () {
        Route::get('admin/dashboard', AdminDashboard::class)->name('admin.dashboard');
        Route::get('admin/perfiles', ProfileManager::class)->name('admin.profiles');
        Route::get('admin/grupos', GroupManager::class)->name('admin.groups');
        Route::get('admin/productos', ProductManager::class)->name('admin.products');
        Route::get('admin/medicamentos', MedicineManager::class)->name('admin.medicines');
        Route::get('admin/stock/ingresos', StockIngresoManager::class)->name('admin.stock.ingresos');
        Route::get('admin/stock/egresos', StockEgresoManager::class)->name('admin.stock.egresos');
        Route::get('admin/stock/historial', StockHistorialManager::class)->name('admin.stock.historial');
        Route::get('admin/clientes', ClientManager::class)->name('admin.clients');
        Route::get('admin/cajas', CajaManager::class)->name('admin.cajas');
        Route::get('admin/ventas', SalesManager::class)->name('admin.sales');
    });
    
    // RUTAS COMPARTIDAS (ADMIN + EMPLEADO)
    Route::middleware(['role:admin|empleado'])->group(function () {
        Route::get('clients', ClientManager::class)->name('clients.index');
    });

    // RUTAS PARA EMPLEADOS (USER)
    Route::get('user/dashboard', UserDashboard::class)->name('user.dashboard');
    
    // Nueva ruta para el Punto de Venta (RF-01 Facturación)
    Route::get('user/ventas', VentaManager::class)->name('ventas.pos');

    // Módulo de Configuración
    Route::get('configuracion', SettingsManager::class)->name('settings.index');
});

// Starter Kit Routes
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});
