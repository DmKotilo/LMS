<?php

namespace File\Providers;

use App\Modules\General\File\src\Interfaces\FileServiceInterface;
use App\Modules\General\File\src\Services\FileService;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class FileModuleServiceProvider extends ServiceProvider
{
    public $bindings = [
        FileServiceInterface::class => FileService::class,
    ];
    public function boot(): void
    {
        $this->routes(function () {
            \Illuminate\Support\Facades\Route::middleware('web')
                ->prefix('file')
                ->group(__DIR__.'/../../routes/file.php');
        });
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

    }
}
