<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Localization;

use Localization\Models\Lang;
use Localization\Models\LangStaticKeyGroup;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Fields\Hidden;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Json;
use MoonShine\UI\Fields\Text;


class LangResource extends ModelResource
{
    protected string $model = Lang::class;

    protected string $title = 'Языки';

    protected bool $isAsync = true;

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название', 'title'),

        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        $fields = [];
        $fieldNamesGroup = [];



        foreach (LangStaticKeyGroup::with('lang_static_keys')->get() as $group) {
            $fieldsKeys = [];
            foreach ($group->lang_static_keys as $key) {
                if ($this->getItem()?->id == null || $this->getItem()->static_translation == null) {
                    $locale = $this->getItem()?->title;
                    $defaultValue = match ($locale) {
                        'en' => $key->default_en ?? $key->default,
                        default => $key->default,
                    };
                } else {
                   /* dd(json_encode($this->getItem()->static_translation, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));*/
                    foreach ($this->getItem()->static_translation as $groupArray) {
                        if($groupArray['group_key'] == $group->title) {
                            foreach ($groupArray['values'] as $keyArray) {
                                if($keyArray['key'] == $key->key) {
                                    $defaultValue = $keyArray['value'];
                                }
                            }
                        }
                    }
                }
                $fieldsKeys[] = [
                    'key' => $key->key,
                    'description' => $key->description,
                    'value' => $defaultValue ?? null,
                ];
            }
            $fieldNamesGroup[] = [
                'group' => $group->description,
                'group_key' => $group->title,
                'values' => $fieldsKeys
            ];
            $fields[] = [
                Text::make('Группа', 'group')->readonly()->style(['opacity: 1']),
                Hidden::make('Префикс группы', 'group_key'),
                Json::make('Значения', 'values')
                    ->fields([
                        Text::make('Ключ', 'key')->readonly()->style(['opacity: 1']),
                        Text::make('Описание', 'description')->readonly()->style(['opacity: 1']),
                        Text::make('Значение', 'value'),
                    ])->stopFilteringEmpty()->creatable(false)->reorderable(false),

            ];
        }
        return [
            ID::make()->sortable(),
            Text::make('Название', 'title')->required(),
            Json::make('Заполните переводы', 'static_translation')->reorderable(false)->vertical(true)->fields(...$fields)->default($fieldNamesGroup)->stopFilteringEmpty()->creatable(false)
        ];
    }



    protected function detailFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название', 'title'),
        ];
    }


    protected function afterCreated(mixed $item): mixed
    {
        $this->json_keys_values_to_file($item);
        return $item;
    }

    protected function afterUpdated(mixed $item): mixed
    {
        $this->json_keys_values_to_file($item);
        return $item;
    }

    protected function json_keys_values_to_file (mixed $item) {

        $langFileArray = [];

        foreach ($item->static_translation as $groupArray) {
            $valuesArray = [];
            foreach ($groupArray['values'] as $value) {
                $keyWithoutPrefix = str_replace($groupArray['group_key'] . '.', '', $value['key']);
                $valuesArray[$keyWithoutPrefix] = $value['value'];
            }
            $langFileArray[$groupArray['group_key']] = $valuesArray;
        }
        $jsonString = json_encode($langFileArray, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        $myFile = "/resources/js/src/shared/i18n/messages/$item->title.json";
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/..'.$myFile,$jsonString);
    }
}
