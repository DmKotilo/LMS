<?php

namespace Recipe\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RecipeModuleServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        $this->routes(function () {
             Route::middleware(['web'])
                ->prefix('recipe')->name('recipe.')
                ->group(__DIR__ . '/../../routes/recipe.php');
        });
       $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
   }
}
