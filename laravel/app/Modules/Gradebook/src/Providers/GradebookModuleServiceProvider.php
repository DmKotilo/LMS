<?php

namespace Gradebook\Providers;

use Gradebook\Models\Gradebook;
use Gradebook\Models\GradebookRow;
use Gradebook\Policies\GradebookPolicy;
use Gradebook\Policies\GradebookRowPolicy;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

class GradebookModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(Gradebook::class, GradebookPolicy::class);
        Gate::policy(GradebookRow::class, GradebookRowPolicy::class);

        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(__DIR__.'/../../routes/api.php');
        });
    }
}
