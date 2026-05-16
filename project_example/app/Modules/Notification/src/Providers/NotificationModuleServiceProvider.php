<?php

namespace Notification\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class NotificationModuleServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        $this->routes(function () {
            Route::middleware(['web'])
                ->prefix('notification')->name('notification.')
                ->group(__DIR__ . '/../../routes/notification.php');
        });
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}
