<?php

namespace Order\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class OrderModuleServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        // Observer регистрируется через атрибут #[ObservedBy] в модели Order
        
        $this->routes(function () {
            Route::middleware(['web'])
                ->prefix('order')->name('order.')
                ->group(__DIR__ . '/../../routes/order.php');
        });
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}
