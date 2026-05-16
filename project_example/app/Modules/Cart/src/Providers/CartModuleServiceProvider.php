<?php

namespace Cart\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class CartModuleServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        $this->routes(function () {
             Route::middleware(['web'])
                ->prefix('cart')->name('cart.')
                ->group(__DIR__ . '/../../routes/cart.php');
        });
       $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
   }
}
