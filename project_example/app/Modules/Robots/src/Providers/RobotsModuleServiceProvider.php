<?php

namespace Robots\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RobotsModuleServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        $this->routes(function () {
             Route::middleware(['web'])
                ->prefix('robots')->name('robots.')
                ->group(__DIR__ . '/../../routes/robots.php');
        });
       $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
   }
}

