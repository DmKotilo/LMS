<?php

namespace Manufacturer\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class ManufacturerModuleServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        $this->routes(function () {
             Route::middleware(['web'])
                ->prefix('manufacturer')->name('manufacturer.')
                ->group(__DIR__ . '/../../routes/manufacturer.php');
        });
       $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
   }
}
