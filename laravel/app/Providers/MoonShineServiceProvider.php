<?php

declare(strict_types=1);

namespace App\Providers;

use App\MoonShine\Resources\Gradebook\GradebookResource;
use App\MoonShine\Resources\MoonShineUserResource;
use App\MoonShine\Resources\MoonShineUserRoleResource;
use App\MoonShine\Resources\User\StudentGroupResource;
use App\MoonShine\Resources\User\StudentProfileResource;
use App\MoonShine\Resources\User\UserResource;
use Illuminate\Support\ServiceProvider;
use MoonShine\Contracts\Core\DependencyInjection\ConfiguratorContract;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Laravel\DependencyInjection\MoonShine;
use MoonShine\Laravel\DependencyInjection\MoonShineConfigurator;

class MoonShineServiceProvider extends ServiceProvider
{
    public function boot(CoreContract $core, ConfiguratorContract $config): void
    {
        $core
            ->resources([
                MoonShineUserResource::class,
                MoonShineUserRoleResource::class,
                UserResource::class,
                StudentGroupResource::class,
                StudentProfileResource::class,
                GradebookResource::class,
            ])
            ->pages([
                ...$config->getPages(),
            ]);
    }
}
