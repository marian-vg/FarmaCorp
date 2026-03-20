<?php

use App\Models\Profile;
use App\Models\User;
use Spatie\Permission\Models\Permission;

it('grants permissions to users via profiles', function () {
    $user = User::factory()->create();
    $permission = Permission::firstOrCreate(['name' => 'test-permission']);
    $profile = Profile::create(['name' => 'Test Profile']);
    $profile->givePermissionTo($permission);

    expect($user->can('test-permission'))->toBeFalse();

    $user->profiles()->attach($profile);

    // Refresh the user to load profiles
    $user->load('profiles');

    expect($user->can('test-permission'))->toBeTrue();
});

it('lists effective permissions correctly', function () {
    $user = User::factory()->create();
    $p1 = Permission::firstOrCreate(['name' => 'p1']);
    $p2 = Permission::firstOrCreate(['name' => 'p2']);

    $user->givePermissionTo($p1);

    $profile = Profile::create(['name' => 'P2 Profile']);
    $profile->givePermissionTo($p2);

    $user->profiles()->attach($profile);
    $user->load('profiles');

    $effective = $user->getAllEffectivePermissions();

    expect($effective->pluck('name'))->toContain('p1', 'p2');
});
