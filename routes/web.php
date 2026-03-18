<?php

use App\Livewire\Actions\SettingsManager;
use App\Livewire\Admin\BackupManager;
use App\Livewire\Admin\CajaManager;
use App\Livewire\Admin\ClientDebtManager;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\GroupManager;
use App\Livewire\Admin\MedicineManager;
use App\Livewire\Admin\ObraSocialManager;
use App\Livewire\Admin\PrescriptionManager;
use App\Livewire\Admin\ProductManager;
use App\Livewire\Admin\ProfileManager;
use App\Livewire\Admin\PromotionManager;
use App\Livewire\Admin\SalesManager;
use App\Livewire\Admin\StockEgresoManager;
use App\Livewire\Admin\StockHistorialManager;
use App\Livewire\Admin\StockIngresoManager;
use App\Livewire\Clients\ClientManager;
use App\Livewire\User\Dashboard as UserDashboard;
use App\Livewire\User\VentaManager;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('livewire.auth.login');
})->name('principal-page')->middleware(['guest']);

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
        if (auth()->user()->hasPermissionTo('admin-panel.acceder')) {
            return redirect()->route('admin.dashboard');
        }

        if (auth()->user()->hasPermissionTo('caja.acceder')) {
            return redirect()->route('user.dashboard');
        }

        abort(403, 'Tu cuenta no tiene permisos operativos asignados. Por favor, contacta a un administrador.');
    })->name('dashboard');

    Route::middleware('permission:admin-panel.acceder')->group(function () {
        Route::get('admin/dashboard', AdminDashboard::class)
            ->name('admin.dashboard');
    });

    Route::get('user/dashboard', UserDashboard::class)->name('user.dashboard')->middleware('permission:caja.acceder');

    // Módulo de Administración Principal
    Route::middleware(['permission:roles.acceder'])->group(function () {
        Route::get('admin/perfiles', ProfileManager::class)->name('admin.profiles');
        Route::get('/admin/mantenimiento', BackupManager::class)->name('admin.backup');
    });

    // Módulos Compartidos / Específicos usando Middleware "permission"
    Route::middleware(['permission:inventario.acceder'])->group(function () {
        Route::get('admin/productos', ProductManager::class)->name('admin.products');
        Route::get('admin/medicamentos', MedicineManager::class)->name('admin.medicines');
        Route::get('admin/grupos', GroupManager::class)->name('admin.groups');
    });

    // Stock
    Route::get('admin/stock/ingresos', StockIngresoManager::class)->name('admin.stock.ingresos')->middleware('permission:stock.ingreso');
    Route::get('admin/stock/egresos', StockEgresoManager::class)->name('admin.stock.egresos')->middleware('permission:stock.egreso');
    Route::get('admin/stock/historial', StockHistorialManager::class)->name('admin.stock.historial')->middleware('permission:stock.acceder');

    // Clientes
    Route::middleware(['permission:clientes.acceder'])->group(function () {
        Route::get('admin/clientes', ClientManager::class)->name('admin.clients');
        Route::get('clients', ClientManager::class)->name('clients.index'); // Antiguo alias
        Route::get('/admin/cuentas-corrientes', ClientDebtManager::class)->name('admin.debts');
    });

    // Caja y Ventas
    Route::get('admin/cajas', CajaManager::class)->name('admin.cajas')->middleware('permission:admin-cajas.acceder');

    Route::middleware(['permission:admin-ventas.acceder'])->group(function () {
        Route::get('admin/ventas', SalesManager::class)->name('admin.sales');
    });

    Route::get('admin/promociones', PromotionManager::class)->name('admin.promotions')->middleware('permission:admin-promociones.acceder');

    Route::get('admin/obras-sociales', ObraSocialManager::class)->name('admin.obras-sociales')->middleware('permission:obrasocial.acceder');

    Route::get('admin/recetas', PrescriptionManager::class)->name('admin.prescriptions')->middleware('permission:recetas.acceder');

    Route::get('user/ventas', VentaManager::class)->name('ventas.pos')->middleware('permission:facturacion.acceder');

    // Acciones específicas emitiendo factura
    Route::get('/factura/imprimir/{id}', [VentaManager::class, 'generarPdfStream'])
        ->name('factura.imprimir')
        ->middleware('permission:facturacion.emitir');

    // Rutas Generales
    Route::get('configuracion', SettingsManager::class)->name('settings.index');
    Route::get('manual', function () {
        return view('manual');
    })->name('manual');
});
