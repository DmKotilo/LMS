<?php

namespace Authorization\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\VKontakte\VKontakteExtendSocialite;

class AuthorizationModuleServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        $this->routes(function () {
             Route::middleware(['web'])
                ->prefix('')->name('')
                ->group(__DIR__ . '/../../routes/auth.php');
        });
       $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        $config = __DIR__.'/../../config/code_verification.php';

        $this->publishes([$config => $config]);
        $this->publishes([$config => base_path('config/code_verification.php')]);

        $this->mergeConfigFrom($config, 'authorization');
        Socialite::extend('vkontakte', function ($app) {
            return new VKontakteExtendSocialite(
                $app['request'],
                $app['config']['services.vk.client_id'],
                $app['config']['services.vk.client_secret'],
                $app['config']['services.vk.redirect'],
                null,
                $app['config']['services.vk.use_openid']
            );
        });
        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('yandex', \SocialiteProviders\Yandex\Provider::class);
        });
        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('vkontakte', \SocialiteProviders\VKontakte\Provider::class);
        });
/*        $this->loadViewsFrom(
            __DIR__ . '/../../Resources/views/auth', // Путь к views
            'auth' // Namespace (должен совпадать с указанным в view())
        );
*/
    }
}
