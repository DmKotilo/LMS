<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Bonus;

use Faker\Core\Number;
use Illuminate\Database\Eloquent\Model;

use Loyalty\Models\LoyaltyLevel;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Text;

/**
 * @extends ModelResource<LoyaltyLevel>
 */
class LoyaltyResource extends ModelResource
{
    protected string $model = LoyaltyLevel::class;

    protected string $title = 'Уровни лояльности';

    /**
     * @return list<FieldContract>
     */

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название','name'),
            \MoonShine\UI\Fields\Number::make('% скидки','discount_percent'),
            \MoonShine\UI\Fields\Number::make('Сумма заказов','coefficient'),
            \MoonShine\UI\Fields\Number::make('Количество дней','duration_days'),
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
                Text::make('Название','name')->required(),
                \MoonShine\UI\Fields\Number::make('% скидки','discount_percent')->required(),
                \MoonShine\UI\Fields\Number::make('Сумма заказов','coefficient')->required(),
                \MoonShine\UI\Fields\Number::make('Количество дней','duration_days')->required(),
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
            Text::make('Название','name'),
            \MoonShine\UI\Fields\Number::make('% скидки','discount_percent'),
            \MoonShine\UI\Fields\Number::make('Сумма заказов','coefficient'),
            \MoonShine\UI\Fields\Number::make('Количество дней','duration_days'),
        ];
    }

    /**
     * @param LoyaltyLevel $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
}
