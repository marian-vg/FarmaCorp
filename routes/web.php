<?php

use App\Livewire\Clients\ClientManager;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\ProfileManager;
use App\Livewire\User\Dashboard as UserDashboard;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('livewire.auth.login');
})->name('principal-page')->middleware(['guest']);


// This will make the login and register routes available to guests and not for users that are already logged in
Route::middleware(['guest'])->group(function () {
    Route::get('login', function () {
        return view('livewire.auth.login');
    })->name('login');
    Route::get('register', function () {
        return view('livewire.auth.register');
    })->name('register');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        if (auth()->user()->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('user.dashboard');
    })->name('dashboard');

    Route::middleware(['role:admin'])->group(function () {
        Route::get('admin/dashboard', AdminDashboard::class)->name('admin.dashboard');
        Route::get('admin/profiles', ProfileManager::class)->name('admin.profiles');
    });

    Route::middleware(['role:admin|empleado'])->group(function () {
        Route::get('clients', ClientManager::class)->name('clients.index');
    });

    Route::get('user/dashboard', UserDashboard::class)->name('user.dashboard');
});

// Starter Kit Routes
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});
