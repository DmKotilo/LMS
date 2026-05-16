<?php

namespace Tag\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class TagModuleServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        $this->routes(function () {
             Route::middleware(['web'])
                ->prefix('tag')->name('tag.')
                ->group(__DIR__ . '/../../routes/tag.php');
        });
       $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
   }
}
