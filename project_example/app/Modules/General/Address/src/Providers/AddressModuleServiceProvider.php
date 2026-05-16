<?php

namespace Address\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class AddressModuleServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        $this->routes(function () {
            Route::middleware('web')
                ->prefix('address')
                ->group(__DIR__ . '/../../routes/address.php');
        });
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}
