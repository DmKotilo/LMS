<?php

namespace App\Traits\Localization;

use App\Models\Localization\Localization;

/**
 * @mixin Localization
 */
trait HasHash
{
    protected static function bootHasHash()
    {
        static::creating(function ($model) {
            $data = [];
            $data[] = $model->localizationable_id;
            $data[] = $model->localizationable_type;
            $data[] = $model->lang_id;
            $data[] = $model->field;
            $hash = static::generateHash($data);
            $model->hash = $hash;
        });

        static::updating(function ($model) {
            $data = [];
            $data[] = $model->localizationable_id;
            $data[] = $model->localizationable_type;
            $data[] = $model->lang_id;
            $data[] = $model->field;
            $hash = static::generateHash($data);
            $model->hash = $hash;
        });


    }

    public static function generateHash(array $values): string
    {
        $hash = md5(implode('', $values));

        return $hash;
    }

}
