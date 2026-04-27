<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

/**
 * Tenant Service Provider
 *
 * Registra helpers e diretivas Blade para trabalhar com multitenancy
 */
class TenantServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar helper para obter empresa atual
        $this->app->singleton('tenant', function ($app) {
            return $app->make('current.company');
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Registrar diretiva Blade @tenant
        Blade::if('tenant', function () {
            return app()->bound('current.company') && app('current.company') !== null;
        });

        // Registrar diretiva Blade @notenant
        Blade::if('notenant', function () {
            return ! app()->bound('current.company') || app('current.company') === null;
        });
    }
}
