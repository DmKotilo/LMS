<?php

namespace App\Models;

use App\Traits\Localization\HasTranslate;
use Illuminate\Database\Eloquent\Model;
use Localization\Models\Lang;
use Localization\Models\Localization;

class TestLocalization extends Model
{
    use HasTranslate;
    protected $fillable =[
      'title'
    ];

    public static function getTransaledField(): array
    {
        return [
            'title' => ['title'=>'Название', 'type'=>'text'],
        ];
    }

    public function TranslateField(string $field): string
    {
        $locale = app()->getLocale();

        if ($locale === 'ru') {
            return $this->getAttribute($field) ?? '';
        }

        $modelId = $this->getKey();
        $modelType = self::class;

        $langId = Lang::where('title', $locale)->value('id');

        if ($langId) {
            $translation = Localization::where([
                ['localizationable_id', $modelId],
                ['localizationable_type', $modelType],
                ['lang_id', $langId],
                ['field', $field],
            ])
                ->value('translate');

            if ($translation) {
                return $translation;
            }
        }

        return $this->getAttribute($field) ?? '';
    }
}
