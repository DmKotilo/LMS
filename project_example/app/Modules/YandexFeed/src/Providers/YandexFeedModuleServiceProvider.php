<?php

namespace YandexFeed\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class YandexFeedModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->routes(function () {
            Route::middleware(['web'])
                ->prefix('feeds/yandex')
                ->name('feeds.yandex.')
                ->group(__DIR__ . '/../../routes/yandex-feed.php');
        });
        
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}

