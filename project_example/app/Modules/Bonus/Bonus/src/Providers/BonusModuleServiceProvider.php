<?php

namespace Bonus\Providers;

use Bonus\Services\BonusService;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class BonusModuleServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        // Регистрация сервиса
        $this->app->singleton(BonusService::class, function ($app) {
            return new BonusService();
        });
    }

    public function boot(): void
    {
        $this->routes(function () {
             Route::middleware(['web','auth:web'])
                ->prefix('bonus')->name('bonus.')
                ->group(__DIR__ . '/../../routes/bonus.php');
        });
        
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
   }
}
