<?php

namespace App\Providers;

use App\View\Composers\BreadcrumbComposer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $files = glob(app_path('Helpers') . "/*.php");
        foreach ($files as $key => $file) {
            require_once $file;
        }
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('Debugbar', \Barryvdh\Debugbar\Facades\Debugbar::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ((env('APP_ENV') !== 'local')) {
            URL::forceScheme(scheme: 'https');
        }
        Vite::prefetch(concurrency: 3);
        if (env('APP_ENV') !== 'local') {
            URL::forceScheme('https');
        }

        JsonResource::withoutWrapping();


        Collection::macro('paginate', function ($perPage = 10) {
            $page = LengthAwarePaginator::resolveCurrentPage('page');

            return new LengthAwarePaginator($this->forPage($page, $perPage), $this->count(), $perPage, $page, [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => request()->query(),
            ]);
        });

        // Регистрируем ViewComposer для breadcrumbs
        View::composer('app', BreadcrumbComposer::class);
    }
}
