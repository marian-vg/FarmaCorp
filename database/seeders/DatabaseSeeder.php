<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Administrador Central',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $adminRole = Role::create(['name' => 'admin']);
        $empleadoRole = Role::create(['name' => 'empleado']);

        $admin->assignRole($adminRole);

        $empleado = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $empleado->assignRole($empleadoRole);

        $this->call([
            RoleAndPermissionSeeder::class,
            VademecumSeeder::class,
            ProfileSeeder::class,
            ObraSocialSeeder::class,
            MedioPagoSeeder::class,
            CajaHistorySeeder::class,
            StockSeeder::class,
        ]);
    }
}
