<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UserTest extends TestCase
{
    // Este trait es MAGIA: Resetea la BD después de cada test para que siempre empieces limpio.
    use RefreshDatabase;

    #[Test]
    public function puede_crear_un_administrador_con_factory(): void
    {
        // 1. Ejecución: Usamos la factory con el estado 'administrador'
        $admin = User::factory()->administrador()->create();

        // 2. Verificación: ¿Se guardó en la BD? ¿Tiene el rol correcto?
        $this->assertDatabaseHas('users', [
            'email' => $admin->email,
            'role' => User::ROLE_ADMINISTRADOR, // Usamos la constante del modelo
        ]);
        
        // Verificamos el Helper
        $this->assertTrue($admin->isAdministrador());
    }

    #[Test]
    public function puede_crear_un_empleado_con_factory(): void
    {
        $empleado = User::factory()->empleado()->create();

        $this->assertDatabaseHas('users', [
            'role' => User::ROLE_EMPLEADO,
        ]);
        
        $this->assertTrue($empleado->isEmpleado());
    }

    #[Test]
    public function el_scope_active_solo_trae_usuarios_activos(): void
    {
        // Creamos 3 usuarios activos
        User::factory()->count(3)->active()->create();
        
        // Creamos 2 usuarios inactivos
        User::factory()->count(2)->inactive()->create();

        // Probamos el Scope
        $usuariosActivos = User::active()->get();

        // Debería haber solo 3
        $this->assertCount(3, $usuariosActivos);
    }
    
    #[Test]
    public function los_usuarios_nuevos_son_activos_por_defecto(): void
    {
        // Creamos un usuario sin especificar estado
        $user = User::factory()->create();
        
        // Debe ser true por defecto (según tu migración)
        $this->assertTrue($user->isActive());
    }
}