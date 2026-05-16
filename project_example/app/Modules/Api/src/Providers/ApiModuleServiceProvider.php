<?php

namespace Api\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\VKontakte\VKontakteExtendSocialite;

class ApiModuleServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        $this->routes(function () {
             Route::middleware(['api'])
                ->prefix('api/v1')->name('api.v1.')
                ->group(__DIR__ . '/../../routes/api_v1.php');
        });

        $config = __DIR__.'/../../config/api_v1.php';

        $this->publishes([$config => $config]);
        $this->publishes([$config => base_path('config/api_v1.php')]);

    }
}
