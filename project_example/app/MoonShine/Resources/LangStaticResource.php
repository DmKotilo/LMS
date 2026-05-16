<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use MoonShine\Core\Resources\Resource;
use MoonShine\Contracts\Core\PageContract;

class LangStaticResource extends Resource
{
    /**
     * @return list<PageContract>
     */
    protected function pages(): array
    {
        return [
            //
        ];
    }
}
