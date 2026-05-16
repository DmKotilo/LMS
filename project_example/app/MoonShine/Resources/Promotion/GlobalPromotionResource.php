<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Promotion;

use App\MoonShine\CustomFields\ImageCustom;
use App\MoonShine\Resources\Product\ComboResource;
use App\MoonShine\Resources\Product\ProductResource;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use GlobalPromotion\Models\GlobalPromotion;
use App\Modules\GlobalPromotion\src\Enums\GlobalPromotionTypeEnum;

/**
 * @extends ModelResource<GlobalPromotion>
 */
class GlobalPromotionResource extends ModelResource
{
    protected string $model = GlobalPromotion::class;

    protected string $title = 'Глобальные акции';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Заголовок','title'),
            Enum::make('Тип','type')->attach(GlobalPromotionTypeEnum::class),
            Switcher::make('Активна','is_active'),
            Date::make('Начало','starts_at'),
            Date::make('Окончание','ends_at'),
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
                Tabs::make([
                    Tab::make('Основное', [
                        Text::make('Заголовок','title')->required(),
                        Textarea::make('Описание','description')->nullable(),
                        Enum::make('Тип','type')->attach(GlobalPromotionTypeEnum::class)->required(),
                        Switcher::make('Активна','is_active')->default(false),
                        Date::make('Начало','starts_at')->required(),
                        Date::make('Окончание','ends_at')->required(),
                    ]),
                    Tab::make('Бонусы/Кэшбэк', [
                        Number::make('Бонусные баллы','bonus_points')->min(0)->step(1)->nullable(),
                        Number::make('Мин. сумма для бонусов','min_order_sum_for_bonus')->min(0)->step(0.01)->nullable(),
                        Number::make('Макс. сумма для бонусов','max_order_sum_for_bonus')->min(0)->step(0.01)->nullable(),
                        Number::make('Кэшбэк %','cashback_percent')->min(0)->max(100)->step(0.01)->nullable(),
                        Number::make('Мин. сумма для кэшбэка','min_order_sum_for_cashback')->min(0)->step(0.01)->nullable(),
                    ]),
                    Tab::make('Привязки', [
                        BelongsToMany::make('Продукты','products','name', ProductResource::class)
                            ->selectMode()
                            ->searchable(),
                        BelongsToMany::make('Комбо','combos','name', ComboResource::class)
                            ->selectMode()
                            ->searchable(),
                    ]),
                ]),
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
            Text::make('Заголовок','title'),
            Textarea::make('Описание','description'),
            Enum::make('Тип','type')->attach(GlobalPromotionTypeEnum::class),
            Switcher::make('Активна','is_active'),
            Date::make('Начало','starts_at'),
            Date::make('Окончание','ends_at'),
            Number::make('Бонусные баллы','bonus_points'),
            Number::make('Мин. сумма для бонусов','min_order_sum_for_bonus'),
            Number::make('Макс. сумма для бонусов','max_order_sum_for_bonus'),
            Number::make('Кэшбэк %','cashback_percent'),
            Number::make('Мин. сумма для кэшбэка','min_order_sum_for_cashback'),
            BelongsToMany::make('Продукты','products','name', ProductResource::class),
            BelongsToMany::make('Комбо','combos','name', ComboResource::class),
        ];
    }
}


