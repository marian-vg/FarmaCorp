<?php

use App\Livewire\Admin\StockIngresoManager;
use App\Livewire\Clients\ClientManager;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    // Asegurar que exista el permiso
    Permission::firstOrCreate(['name' => 'stock.ingreso']);
    Permission::firstOrCreate(['name' => 'clientes.crear_editar']);
});

it('blocks access to protected routes for unauthorized users', function () {
    $user = User::factory()->create();

    // Este usuario no tiene stock.ingreso, intentamos acceder a la ruta
    $response = $this->actingAs($user)->get(route('admin.stock.ingresos'));

    // Debido a nuestro handler global en bootstrap/app.php modificado antes,
    // retorna redirect back con sesión flash notify en lugar de 403 plano.
    // Si no ha redirigido atras, puede que redirija al home o login.
    // Verifica que NO fue un 200 OK
    expect($response->status())->not->toBe(200);
});

it('allows access to protected routes for authorized users', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('stock.ingreso');

    $response = $this->actingAs($user)->get(route('admin.stock.ingresos'));

    $response->assertSuccessful();
});

it('allows livewire actions for authorized users', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('stock.ingreso');

    $this->actingAs($user);

    // No fallará por autorización, fallará por validación (lo cual está bien, significa que pasó el Authorize)
    Livewire::test(StockIngresoManager::class)
        ->call('save')
        ->assertHasErrors();
});

it('allows client manager edit actions when authorized', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('clientes.crear_editar');

    $this->actingAs($user);

    Livewire::test(ClientManager::class)
        ->call('createClient')
        ->assertOk();
});
