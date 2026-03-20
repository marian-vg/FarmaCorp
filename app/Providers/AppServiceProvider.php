<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
                if ($profile->hasDirectPermission($ability)) {
                    return true;
                }
            }
        });

        Model::preventLazyLoading(! app()->isProduction());
    }
}
