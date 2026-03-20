<?php

namespace Database\Seeders;

use App\Models\Profile;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profiles = [
            [
                'name' => 'Caja Turno Mañana',
                'description' => 'Perfil para operadores de caja en el horario matutino.',
                'permissions' => ['caja.acceder', 'caja.abrir', 'caja.ingresos_egresos', 'facturacion.acceder', 'facturacion.emitir'],
            ],
            [
                'name' => 'Caja Turno Tarde',
                'description' => 'Perfil para operadores de caja en el horario vespertino.',
                'permissions' => ['caja.acceder', 'caja.abrir', 'caja.ingresos_egresos', 'facturacion.acceder', 'facturacion.emitir'],
            ],
            [
                'name' => 'Administrador de Obras Sociales',
                'description' => 'Gestión completa de convenios y vademécums de obras sociales.',
                'permissions' => ['obrasocial.acceder', 'obrasocial.crear_editar'],
            ],
            [
                'name' => 'Gestor de Inventario y Stock',
                'description' => 'Responsable del control de existencias, ingresos y egresos de mercadería.',
                'permissions' => ['inventario.acceder', 'stock.acceder', 'stock.ingreso', 'stock.egreso'],
            ],
            [
                'name' => 'Auditor de Ventas y Recetas',
                'description' => 'Perfil administrativo para la revisión de ventas y archivo de recetas médicas.',
                'permissions' => ['admin-ventas.acceder', 'recetas.acceder', 'recetas.crear_editar'],
            ],
            [
                'name' => 'Atención al Cliente',
                'description' => 'Gestión de legajos de clientes y vinculación con obras sociales.',
                'permissions' => ['clientes.acceder', 'clientes.crear_editar'],
            ],
        ];

        foreach ($profiles as $pData) {
            $profile = Profile::firstOrCreate(
                ['name' => $pData['name']],
                ['description' => $pData['description']]
            );

            // Sincronizar permisos del perfil (HasPermissions trait de Spatie en Profile model)
            $permissions = Permission::whereIn('name', $pData['permissions'])->get();
            $profile->syncPermissions($permissions);
        }
    }
}
