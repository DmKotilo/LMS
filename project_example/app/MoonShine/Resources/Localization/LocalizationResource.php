<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Localization;

use Illuminate\Database\Eloquent\Model;
use Localization\Models\Lang;
use Localization\Models\Localization;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\MorphTo;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;


class LocalizationResource extends ModelResource
{
    public function __construct(
        private ?array $transaledFields = null,
        private ?array $transaledFieldsType = null
    ) {
    }

    protected string $model = Localization::class;

    protected string $title = 'Перевод';





    public function formFields(): array
    {
        $translatedFields = [];
        $translatedFieldsType = [];
        if ($this->transaledFields){
            foreach ($this->transaledFields as $key => $field) {
                $transaledFields[$key] = $field["title"];
                $this->transaledFieldsType[$key] = $field ?? null;
            }
        }
        elseif (isset($this->getItem()->localizationable) && $this->getItem()->localizationable->getTransaledField()){
            foreach ($this->getItem()->localizationable->getTransaledField() as $key => $field) {
                $transaledFields[$key] = $field["title"];
                $this->transaledFieldsType[$key] = $field ?? null;
            }
        }

        return [
            Box::make([
                ID::make()->sortable(),
                Select::make('Поле', 'field')->options($transaledFields ?? []),
                Text::make('Перевод', 'translate'),
                BelongsTo::make('Язык', 'lang', fn ($lang) => $lang->title, resource: LangResource::class),
                MorphTo::make('Переведенный объект', 'localizationable'),
            ]),
        ];
    }

    public function indexFields(): array
    {
        $translatedFields = [];
        $translatedFieldsType = [];
        if ($this->transaledFields){
            foreach ($this->transaledFields as $key => $field) {
                $transaledFields[$key] = $field["title"];
                $this->transaledFieldsType[$key] = $field ?? null;
            }
        }
        elseif (isset($this->getItem()->localizationable) && $this->getItem()->localizationable->getTransaledField()){
            foreach ($this->getItem()->localizationable->getTransaledField() as $key => $field) {
                $transaledFields[$key] = $field["title"];
                $this->transaledFieldsType[$key] = $field ?? null;
            }
        }

        return [
                ID::make()->sortable(),
                Select::make('Поле', 'field')->options($transaledFields ?? []),
                Text::make('Перевод', 'translate'),
                BelongsTo::make('Язык', 'lang', fn ($lang) => $lang->title, resource: LangResource::class),
        ];
    }



    public function prepareForValidation(): void
    {
        $data = [];
        $data[] = request()->input('localizationable_id');
        $data[] = request()->input('localizationable_type');
        $data[] = request()->input('lang_id');
//        $data[] = request()->input('field');

//        request()->merge([
//            'hash' => Localization::generateHash($data),
//        ]);
    }

    public function getTranslatedFieldsFormByItem(Model $item): Tabs
    {
        $translatedFields = method_exists($item, 'getTransaledField') ? $item::getTransaledField() : [];
        $languages = Lang::all();

        return Tabs::make(
            $languages->mapWithKeys(function ($lang) use ($translatedFields, $item) {
                $fields = [];

                foreach ($translatedFields as $field => $meta) {
                    $title = $meta['title'] ?? $field;
                    $type = $meta['type'] ?? 'text';

                    $value = Localization::query()
                        ->where('localizationable_type', get_class($item))
                        ->where('localizationable_id', $item->id)
                        ->where('lang_id', $lang->id)
                        ->where('field', $field)
                        ->value('translate') ?? '';

                    $fieldComponent = match ($type) {
                        'textarea' => Textarea::make($title, "translations[{$lang->id}][{$field}]")->setValue($value),
                        default => Text::make($title, "translations[{$lang->id}][{$field}]")->setValue($value),
                    };

                    $fields[] = $fieldComponent;
                }

                return [
                    $lang->title => Tab::make($lang->title, $fields),
                ];
            })->toArray()
        );
    }




}
