<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar el cache de permisos
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Estructura de Permisos por Grupo
        $permissionsByGroup = [
            'Usuarios' => [
                ['name' => 'usuarios.acceder', 'display_name' => 'Acceder a Usuarios'],
                ['name' => 'usuarios.crear', 'display_name' => 'Crear Usuarios'],
                ['name' => 'usuarios.editar', 'display_name' => 'Editar Usuarios'],
                ['name' => 'usuarios.desactivar', 'display_name' => 'Desactivar Usuarios'],
                ['name' => 'usuarios.reactivar', 'display_name' => 'Reactivar Usuarios'],
                ['name' => 'usuarios.roles.modificar', 'display_name' => 'Modificar Roles de Usuario'],
                ['name' => 'usuarios.permisos.modificar', 'display_name' => 'Modificar Permisos de Usuario'],
                ['name' => 'usuarios.perfiles.modificar', 'display_name' => 'Modificar Perfiles de Usuario'],
                ['name' => 'usuarios.password.modificar', 'display_name' => 'Modificar Contraseña de Usuario'],
            ],
            'Roles y Perfiles' => [
                ['name' => 'roles.acceder', 'display_name' => 'Acceder a Roles'],
                ['name' => 'roles.crear_editar', 'display_name' => 'Crear / Editar Roles'],
                ['name' => 'roles.eliminar', 'display_name' => 'Eliminar Roles'],
            ],
            'Configuración' => [
                ['name' => 'configuracion.modificar', 'display_name' => 'Modificar Configuración del Sistema'],
            ],
            'Inventario' => [
                ['name' => 'inventario.acceder', 'display_name' => 'Acceder al Inventario'],
                ['name' => 'inventario.crear_editar', 'display_name' => 'Crear / Editar Producto/Medicamento'],
                ['name' => 'inventario.desactivar', 'display_name' => 'Desactivar Producto/Medicamento'],
            ],
            'Stock' => [
                ['name' => 'stock.acceder', 'display_name' => 'Acceder a Stock/Kardex'],
                ['name' => 'stock.ingreso', 'display_name' => 'Registrar Ingreso'],
                ['name' => 'stock.egreso', 'display_name' => 'Registrar Egreso'],
            ],
            'Caja' => [
                ['name' => 'caja.acceder', 'display_name' => 'Acceder a la terminal de Caja'],
                ['name' => 'caja.abrir', 'display_name' => 'Abrir Caja'],
                ['name' => 'caja.cerrar', 'display_name' => 'Cerrar Caja'],
                ['name' => 'caja.ingresos_egresos', 'display_name' => 'Registrar Ingreso/Egreso Manual'],
            ],
            'Facturación' => [
                ['name' => 'facturacion.acceder', 'display_name' => 'Acceder a módulo de Facturación'],
                ['name' => 'facturacion.emitir', 'display_name' => 'Emitir Factura/Comprobante'],
            ],
            'Obras Sociales' => [
                ['name' => 'obrasocial.acceder', 'display_name' => 'Acceder a Obras Sociales'],
                ['name' => 'obrasocial.crear_editar', 'display_name' => 'Crear / Editar Obras Sociales'],
            ],
            'Recetas' => [
                ['name' => 'recetas.acceder', 'display_name' => 'Acceder a Recetas'],
                ['name' => 'recetas.crear_editar', 'display_name' => 'Crear / Editar Recetas'],
            ],
            'Clientes' => [
                ['name' => 'clientes.acceder', 'display_name' => 'Acceder a Clientes'],
                ['name' => 'clientes.crear_editar', 'display_name' => 'Crear / Editar Clientes'],
                ['name' => 'clientes.desactivar', 'display_name' => 'Desactivar Clientes'],
            ],
            'Sistema' => [
                ['name' => 'admin-panel.acceder', 'display_name' => 'Acceder al Panel de Administración'],
                ['name' => 'admin-cajas.acceder', 'display_name' => 'Administrar Cajas e Historial'],
                ['name' => 'admin-ventas.acceder', 'display_name' => 'Administrar Ventas e Historial'],
                ['name' => 'admin-promociones.acceder', 'display_name' => 'Administrar Promociones'],
            ],
        ];

        // Crear los permisos
        foreach ($permissionsByGroup as $groupName => $permissions) {
            foreach ($permissions as $permissionData) {
                Permission::firstOrCreate(
                    ['name' => $permissionData['name']],
                    [
                        'display_name' => $permissionData['display_name'],
                        'group_name' => $groupName,
                    ]
                );
            }
        }

        // Crear el rol Super Admin y asignarle todos los permisos
        $roleInfo = [
            'name' => 'super-admin',
            'display_name' => 'Super Administrador',
            'description' => 'Rol con acceso total al sistema',
        ];

        // Verificar si la tabla roles tiene estos campos antes de crear (los agregados anteriormente a profiles o si la idea era extender).
        // En migraciones vimos solo description, display_name lo tiene permissions
        // Vamos a verificar si Roles tiene display_name, no lo vimos en migrations, Spatie defaults a solo name.
        // Lo crearemos básico para Spatie e iremos ajustando si es necesario.
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);

        // Asignar todos los permisos creados
        $superAdmin->syncPermissions(Permission::all());

        // Opcional: Asignar este rol al primer usuario (si existe)
        $firstUser = User::first();
        if ($firstUser) {
            $firstUser->assignRole($superAdmin);
        }
    }
}
