<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. EL ADMINISTRADOR DE PRUEBA (Para que tú entres)
        User::factory()
            ->administrador()
            ->active()
            ->withoutTwoFactor() // ¡Vital para no bloquearte!
            ->create([
                'name' => 'Admin Principal',
                'email' => 'admin@farmacorp.com',
                'password' => Hash::make('password'),
            ]);

        // 2. OTRO ADMIN ALEATORIO (Para cumplir con los 2 admins)
        User::factory()
            ->administrador()
            ->active()
            ->withoutTwoFactor()
            ->create();

        // 3. UN EMPLEADO DE PRUEBA (Para probar permisos limitados)
        User::factory()
            ->empleado()
            ->active()
            ->withoutTwoFactor()
            ->create([
                'name' => 'Empleado Test',
                'email' => 'empleado@farmacorp.com',
                'password' => Hash::make('password'),
            ]);

        // 4. EL RESTO DE LOS EMPLEADOS (17 restantes para llegar a 18 empleados)
        User::factory()
            ->count(17)
            ->empleado()
            ->active()
            ->withoutTwoFactor()
            ->create();
    }
}