<?php

namespace Alert\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class AlertModuleServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        $this->routes(function () {
            Route::middleware(['web'])
                ->prefix('alert')->name('alert.')
                ->group(__DIR__ . '/../../routes/alert.php');
        });
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}
