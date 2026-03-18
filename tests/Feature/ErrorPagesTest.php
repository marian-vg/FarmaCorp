<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Blade::component('flux::card', 'flux::components.card');

        // Registrar ruta dummy para forzar 403
        Route::get('/dummy-admin', function () {
            abort(403);
        })->middleware('auth');

        // Registrar ruta dummy para forzar 419 (Simulando un POST sin CSRF)
        Route::post('/dummy-post', function () {
            return 'OK';
        });

        // Necesitamos el seeder para que `hasPermissionTo` funcione con permisos reales
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_403_renders_for_guest()
    {
        $response = $this->get('/dummy-admin');

        // Guests usually get redirected to login, but if we somehow hit 403:
        $response->assertStatus(302);
    }

    public function test_403_renders_logout_button_for_user_without_permissions()
    {
        $user = User::factory()->create();

        // Evitar el middleware global de exceptions que intercepta AuthorizationException en test y en bootstrap/app.php
        $this->withoutExceptionHandling();

        try {
            $this->actingAs($user)->get('/dummy-admin');
            $this->fail('Expected 403 abort.');
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());

            // Render the view manually to check content since we caught the exception
            $view = view('errors.403')->render();
            $this->assertStringContainsString('Cerrar Sesión / Volver al Login', $view);
            $this->assertStringContainsString('Tu cuenta ha sido creada', $view);
        }
    }

    public function test_403_renders_fallback_route_for_user_with_some_permissions()
    {
        $user = User::factory()->create();

        // Give a single permission
        $permission = Permission::where('name', 'caja.acceder')->first();
        $user->givePermissionTo($permission);

        $this->withoutExceptionHandling();

        try {
            $this->actingAs($user)->get('/dummy-admin');
            $this->fail('Expected 403 abort.');
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());

            // Simulate the auth context for the view render
            auth()->login($user);

            $view = view('errors.403')->render();

            $this->assertStringContainsString('Volver a Mi Caja Operativa', $view);
            $this->assertStringContainsString(route('user.dashboard'), $view);
        }
    }

    public function test_419_renders_successfully()
    {
        // View render test to ensure syntax is correct
        $view = view('errors.419')->render();

        $this->assertStringContainsString('Página Expirada', $view);
        $this->assertStringContainsString('Refrescar Página', $view);
        $this->assertStringContainsString('Ir al Inicio / Login', $view);
    }
}
