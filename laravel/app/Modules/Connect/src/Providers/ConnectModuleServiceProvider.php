<?php

namespace Connect\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class ConnectModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(__DIR__.'/../../routes/api.php');
        });
    }
}
