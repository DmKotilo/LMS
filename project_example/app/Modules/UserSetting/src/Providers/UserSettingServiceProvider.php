<?php

namespace UserSetting\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use UserSetting\Models\UserSetting;
use UserSetting\Observers\UserSettingObserver;

class UserSettingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->routes(function () {
            Route::middleware(['auth:web'])
                ->prefix('usersetting')->name('usersetting.')
                ->group(__DIR__ . '/../../routes/usersetting.php');
        });

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        UserSetting::observe(UserSettingObserver::class);
    }
}
