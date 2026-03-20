<?php

namespace App\Livewire\Actions;

use Livewire\Component;

class ErrorFallbackActions extends Component
{
    public ?string $fallbackRoute = null;

    public string $fallbackName = '';

    public function mount()
    {
        $this->evaluateFallback();
    }

    public function getListeners(): array
    {
        if (auth()->check()) {
            $userId = auth()->id();

            return [
                "echo-private:App.Models.User.{$userId},UserPermissionsUpdated" => 'evaluateFallback',
            ];
        }

        return [];
    }

    public function evaluateFallback()
    {
        $this->fallbackRoute = null;
        $this->fallbackName = '';

        if (auth()->check()) {
            $permissionsMap = [
                'admin-panel.acceder' => ['route' => 'admin.dashboard', 'name' => 'Panel de Administración'],
                'inventario.acceder' => ['route' => 'admin.products', 'name' => 'Inventario'],
                'stock.ingreso' => ['route' => 'admin.stock.ingresos', 'name' => 'Ingreso de Stock'],
                'stock.egreso' => ['route' => 'admin.stock.egresos', 'name' => 'Egreso de Stock'],
                'stock.acceder' => ['route' => 'admin.stock.historial', 'name' => 'Historial de Stock'],
                'roles.acceder' => ['route' => 'admin.profiles', 'name' => 'Perfiles y Accesos'],
                'clientes.acceder' => ['route' => 'admin.clients', 'name' => 'Clientes'],
                'admin-ventas.acceder' => ['route' => 'admin.sales', 'name' => 'Ventas'],
                'recetas.acceder' => ['route' => 'admin.prescriptions', 'name' => 'Archivo de Recetas'],
                'admin-promociones.acceder' => ['route' => 'admin.promotions', 'name' => 'Promociones'],
                'obrasocial.acceder' => ['route' => 'admin.obras-sociales', 'name' => 'Obras Sociales'],
                'facturacion.acceder' => ['route' => 'ventas.pos', 'name' => 'Punto de Venta'],
                'caja.acceder' => ['route' => 'user.caja', 'name' => 'Mi Caja Operativa'],
                'admin-cajas.acceder' => ['route' => 'admin.cajas', 'name' => 'Administración de Cajas'],
            ];

            // Reload user permissions to ensure freshness
            auth()->user()->forgetCachedPermissions();

            foreach ($permissionsMap as $permission => $data) {
                if (auth()->user()->hasPermissionTo($permission)) {
                    $this->fallbackRoute = route($data['route']);
                    $this->fallbackName = $data['name'];
                    break;
                }
            }
        }
    }

    public function render()
    {
        return view('livewire.actions.error-fallback-actions');
    }
}
