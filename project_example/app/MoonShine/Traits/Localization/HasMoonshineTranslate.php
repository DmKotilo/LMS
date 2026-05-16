<?php

namespace App\MoonShine\Traits\Localization;

use App\MoonShine\Resources\Localization\LocalizationResource;
use MoonShine\Laravel\Fields\Relationships\MorphMany;

trait HasMoonshineTranslate
{
    protected function getTranslateRelation(array $transaledFields): MorphMany
    {
//        dd(1);
        return MorphMany::make(
            'Переводы',
            'localization',
            resource: new LocalizationResource($transaledFields),
        )->creatable();
    }
}
