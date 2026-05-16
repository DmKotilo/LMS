<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Setting;

use Illuminate\Database\Eloquent\Model;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use Setting\Models\Setting;

/**
 * @extends ModelResource<Setting>
 */
class SettingResource extends ModelResource
{
    protected string $model = Setting::class;

    protected string $title = 'Настройки сайта';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            \MoonShine\UI\Fields\Text::make('Ключ','key'),
            Text::make('Значение','value'),
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make([
                ID::make(),
                Text::make('Ключ','key')->required(),
                Text::make('Значение','value')->required(),
                Select::make('Тип значения','value_type')  ->options([
                    'integer' => 'Число',
                    'boolean' => 'True/False',
                    'string' => 'Cтрока',
                    'float' => 'Число с остатком',
                ])->required(),
                Textarea::make('Описание','description')->nullable()
            ])
        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function detailFields(): iterable
    {
        return [
            ID::make(),
            Text::make('Ключ','key')->required(),
            Text::make('Значение','value')->required(),
            Select::make('Тип значения','value_type')  ->options([
                'integer' => 'Число',
                'boolean' => 'True/False',
                'string' => 'Cтрока',
                'float' => 'Число с остатком',
            ])->required(),
            Textarea::make('Описание','description')->nullable()
        ];
    }

    /**
     * @param Setting $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        $rules = [
            'key' => ['required', 'string', 'max:255'],
            'value' => ['required'],
            'value_type' => ['required', 'in:integer,boolean,string,float'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];

        $bonusSettingKeys = [
            'referral_reward_inviter',
            'referral_reward_invited',
            'subscribe_mails_bonus',
            'review_bonus',
        ];

        // Получаем ключ из запроса (может быть изменен при редактировании) или из модели
        $key = request()->input('key') ?? $item->key ?? null;

        // Если это настройка бонусов, добавляем ограничения
        if ($key && in_array($key, $bonusSettingKeys)) {
            $rules['value'][] = 'integer';
            $rules['value'][] = 'min:0';
            $rules['value'][] = 'max:10000';
        }

        return $rules;
    }

    protected function beforeSaving(mixed $item): mixed
    {
        // Определяем ключи настроек бонусов, которые должны быть ограничены
        $bonusSettingKeys = [
            'referral_reward_inviter',
            'referral_reward_invited',
            'subscribe_mails_bonus',
            'review_bonus',
        ];

        // Получаем ключ из запроса (может быть изменен при редактировании) или из модели
        $key = request()->input('key') ?? $item->key ?? null;

        if ($key && in_array($key, $bonusSettingKeys)) {
            // Убеждаемся, что тип значения - integer
            if ($item->value_type !== 'integer') {
                throw new \Exception('Настройки бонусов должны иметь тип "integer"');
            }

            // Преобразуем значение в integer и проверяем ограничение
            $value = (int) $item->value;
            if ($value < 0 || $value > 10000) {
                throw new \Exception('Количество баллов должно быть от 0 до 10000');
            }

            $item->value = (string) $value;
        }

        return $item;
    }
}
