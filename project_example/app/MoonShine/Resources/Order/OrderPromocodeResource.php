<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Order;

use App\Modules\Order\src\Enums\OrderStatusEnum;
use App\MoonShine\Resources\User\UserResource;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use MoonShine\AssetManager\InlineJs;
use MoonShine\AssetManager\Js;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\DTOs\Select\Option;
use MoonShine\Support\DTOs\Select\OptionProperty;
use MoonShine\Support\DTOs\Select\Options;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;
use Order\Enums\OrderPromocodeStatusEnum;
use Order\Models\Order;
use Order\Models\OrderPromocode;

/**
 * @extends ModelResource<OrderPromocode>
 */
class OrderPromocodeResource extends ModelResource
{
    protected string $model = OrderPromocode::class;

    protected string $title = 'Промокоды';

    protected bool $isAsync = false;

    protected function modifyQueryBuilder(Builder $builder): Builder
    {
        return $builder;
    }

    protected function modifyItemQueryBuilder(Builder $builder): Builder
    {
        return $builder;
    }

    public function getItems(): \Illuminate\Support\Collection|\Illuminate\Contracts\Pagination\CursorPaginator|\Illuminate\Contracts\Pagination\Paginator|\Illuminate\Support\LazyCollection
    {
        $items = parent::getItems();

        $totals = Order::query()
            ->whereNotNull('order_promocode_id')
            ->selectRaw('order_promocode_id, COALESCE(SUM(COALESCE(COALESCE(NULLIF(price_final_1c, 0), price_final), 0)), 0) as orders_sum_price_final')
            ->groupBy('order_promocode_id')
            ->pluck('orders_sum_price_final', 'order_promocode_id');

        $collection = $items instanceof \Illuminate\Pagination\AbstractPaginator
            ? $items->getCollection()
            : \Illuminate\Support\Collection::wrap($items);
        $collection->each(function (mixed $item) use ($totals): void {
            if ($item instanceof OrderPromocode) {
                $item->setAttribute('orders_sum_price_final', (int) round((float) ($totals->get($item->getKey(), 0)), 0));
            }
        });

        return $items;
    }

    public function findItem(bool $orFail = false): mixed
    {
        $item = parent::findItem($orFail);
        if ($item instanceof OrderPromocode) {
            $sum = Order::query()
                ->where('order_promocode_id', $item->getKey())
                ->selectRaw('COALESCE(SUM(COALESCE(COALESCE(NULLIF(price_final_1c, 0), price_final), 0)), 0) as s')
                ->value('s');
            $item->setAttribute('orders_sum_price_final', (int) round((float) ($sum ?? 0), 0));
        }
        return $item;
    }

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Промокод','promocode'),
            Enum::make('Статус','status')->attach(OrderPromocodeStatusEnum::class),
            Number::make('Скидка, %','percent'),
            Number::make('Применений','number_applications'),
            Number::make('Сумма заказов, ₽','orders_sum_price_final')
                ->nullable()
                ->step(1)
                ->readonly(),
            Preview::make('Комментарий', 'description', fn ($item) => '<div style="max-width: 300px; white-space: pre-wrap; word-break: break-word; line-height: 1.3;">' . e($item->description ?? '') . '</div>'),
            // Number::make('Лимит применений','exceeded_limit')->nullable(),

        ];
    }

    protected function formFields(): iterable
    {

/*        $selectOptions = [];

        foreach (OrderPromocodeStatusEnum::cases() as $case) {
            $selectOptions[] =  new Option(
                label: $case->toString(),
                value: $case->value,
                properties: new OptionProperty($case->disabledSelect()),
                selected: $this->getItem()?->status?->value == $case->value ? true : false,
            );
        }*/

        return [
            Box::make([
                ID::make()->sortable(),
                Text::make('Промокод','promocode'),
                Text::make('Комментарий','description'),
/*                Select::make('status')->options(OrderPromocodeStatusEnum::toArray()),*/
                Enum::make('Статус','status')->attach(OrderPromocodeStatusEnum::class)
                    ->required()->native(),

/*                Select::make('Статус','status')->options(
                    new Options($selectOptions)
                ),*/

                Number::make('Процент скидки','percent')->required()->min(1)->max(40),
                Flex::make([
                    Date::make('Дата начала действия','date_from')->withTime()->nullable(),
                    Date::make('Дата окончания действия','date_to')->withTime()->nullable(),
                ]),
                Number::make('Количество применений','number_applications')->default(0)->required(),
                Number::make('Лимит применений','exceeded_limit')->nullable(),
                HasMany::make('Заказы', 'orders', 'id', OrderResource::class)
                    ->disableOutside(),
            ])
        ];
    }
/*    case active = 'active';
    case inactive = 'inactive'; //отключен вручную
    case expired = 'expired'; //неактивен срок действия
    case exceeded_limit = 'exceeded_limit'; //достигнут лимит количества применений*/



    protected function assets(): array
    {
        return [
            InlineJs::make(<<<'JS'
                    document.addEventListener("DOMContentLoaded", function() {
                        document.querySelectorAll("select[name='status'] option").forEach(opt => {
                            if (opt.value == "expired") opt.disabled = true;
                            if (opt.value == "exceeded_limit") opt.disabled = true;
                        });
                    });
                JS),
        ];
    }

    protected function detailFields(): iterable
    {
        return [
            ID::make(),
            Text::make('Промокод', 'promocode'),
            Enum::make('Статус', 'status')->attach(OrderPromocodeStatusEnum::class),
            Number::make('Скидка, %', 'percent'),
            Number::make('Кол-во применений', 'number_applications'),
            Number::make('Сумма заказов, руб.', 'orders_sum_price_final')
                ->nullable()
                ->step(1)
                ->readonly(),
            Number::make('Лимит применений', 'exceeded_limit')->nullable(),
            Text::make('Комментарий', 'description'),
            HasMany::make('Заказы', 'orders', 'id', OrderResource::class)
                ->disableOutside(),
        ];
    }

    protected function rules(mixed $item): array
    {
        return [];
    }
}
