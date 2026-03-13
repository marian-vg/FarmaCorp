<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        $adminRole = Role::create(['name' => 'admin']);
        $empleadoRole = Role::create(['name' => 'empleado']);

        Permission::create(['name' => 'create-user']);
        Permission::create(['name' => 'modify-user']);

        $adminRole->givePermissionTo('create-user', 'modify-user');

        $user->assignRole($adminRole);

        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        $testUser->assignRole($empleadoRole);

        $this->call([
            MedioPagoSeeder::class,
            CajaHistorySeeder::class,
            VademecumSeeder::class,
            RoleAndPermissionSeeder::class,
        ]);
    }
}
