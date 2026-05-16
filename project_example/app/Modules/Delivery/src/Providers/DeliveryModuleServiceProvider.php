<?php

namespace Delivery\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class DeliveryModuleServiceProvider extends ServiceProvider
{

    public function boot(): void
    { 
        $this->routes(function () {
             Route::middleware(['web'])
                ->prefix('delivery')
                ->group(__DIR__ . '/../../routes/delivery.php');
        });
       $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
   }
}