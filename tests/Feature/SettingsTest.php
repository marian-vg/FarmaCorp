<?php

use App\Models\User;

it('loads the settings page for authenticated users with theme toggle', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('settings.index'));

    $response->assertStatus(200);
    $response->assertSee('Configuración del Sistema');
    $response->assertSee('Preferencia Visual');
    $response->assertSee('Modo de Visualización');
});

it('redirects guests to login when accessing settings', function () {
    $response = $this->get(route('settings.index'));

    $response->assertRedirect(route('login'));
});
