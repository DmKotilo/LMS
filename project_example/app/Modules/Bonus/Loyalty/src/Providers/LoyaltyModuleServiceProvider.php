<?php

namespace Loyalty\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class LoyaltyModuleServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        $this->routes(function () {
             Route::middleware(['web','auth:web'])
                ->prefix('loyalty')->name('loyalty.')
                ->group(__DIR__ . '/../../routes/loyalty.php');
        });
       $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
   }
}
