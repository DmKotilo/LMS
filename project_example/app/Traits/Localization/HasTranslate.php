<?php

namespace App\Traits\Localization;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Localization\Models\Lang;
use Localization\Models\Localization;

/**
 * @template T of Model
 * @template TKey of string Model field
 * @template TVal of string Field display name
 */
trait HasTranslate
{

    public static function bootHasTranslate()
    {
        static::created(function (Model $model) {
            foreach (Lang::all() as $lang) {
                foreach ($model->getTransaledField() as $key => $field) {
                    if (!empty($model->$key)) {
                        Localization::updateOrCreate(
                            [
                                'localizationable_type' => $model->getMorphClass(),
                                'localizationable_id' => $model->id,
                                'lang_id' => $lang->id,
                                'field' => $key,
                            ],
                            [
                                'translate' => $model->getAttribute($key),
                            ]
                        );
                    }
                }
            }
        });


        static::updated(function (Model $model) {
            foreach (Lang::all() as $lang) {
                foreach ($model->getTransaledField() as $key => $field) {
                    if ($model->isDirty($key) && !empty($model->$key)) {
                        Localization::firstOrCreate(
                            [
                            'localizationable_type' => $model->getMorphClass(),
                            'localizationable_id' => $model->id,
                            'lang_id' => $lang->id,
                            'field' => $key,
                            ],
                            [
                                'localizationable_type' => $model->getMorphClass(),
                                'localizationable_id' => $model->id,
                                'lang_id' => $lang->id,
                                'field' => $key,
                                'translate' => $model->translate($key, 'ru')
                            ]);
                    }
                }
            }
        });

        static::deleting(function (Model $model) {
            $model->localization()->delete();
        });


    }

    /**
     * @return array<TKey, TVal>
     */
    abstract public static function getTransaledField(): array;

    public function translate(string $field, ?string $lang = null)
    {
        $lang = $lang ?? app()->getLocale();
        if ($lang === 'ru') {
            return $this->getAttribute($field) ?? '';
        }

        $modelId = $this->getKey();
        $modelType = self::class;

        $langId = Lang::where('title', $lang)->value('id');
        if ($langId) {
            $translation = Localization::where([
                ['localizationable_id', $modelId],
                ['localizationable_type', $modelType],
                ['lang_id', $langId],
                ['field', $field],
            ])->value('translate');

            if ($translation) {
                return $translation;
            }
        }

        return $this->getAttribute($field) ?? '';
    }


    public function localization(): MorphMany
    {
        /** @var T $this */
        return $this->morphMany(Localization::class, 'localizationable');
    }

}
