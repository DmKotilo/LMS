<?php

namespace User\Providers;

use App\Http\Middleware\EnsureVerified;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use User\Models\User;
use User\Observers\UserObserver;

class UserModuleServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        User::observe(UserObserver::class);

        $this->routes(function () {
             Route::middleware(['web','auth:web',EnsureVerified::class])
                ->prefix('user')->name('user.')
                ->group(__DIR__ . '/../../routes/user.php');
        });
       $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
   }
}
