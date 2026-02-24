<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::after(function ($user, $ability) {
            // Check if any of the user's assigned custom profiles have the requested permission.
            foreach ($user->profiles as $profile) {
                if ($profile->hasPermissionTo($ability)) {
                    return true;
                }
            }
        });
    }
}
