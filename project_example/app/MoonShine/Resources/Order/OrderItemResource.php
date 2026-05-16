<?php

namespace App\MoonShine\Resources\Order;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Number;
use Order\Models\OrderItem;
use App\MoonShine\Resources\Product\ProductResource;

class OrderItemResource extends ModelResource
{
    protected string $model = OrderItem::class;
    protected string $title = 'Товары заказа';

    protected function indexFields(): iterable
    {
        return [
            ID::make(),
            Text::make('Название товара', 'item.name'),
            Number::make('Цена за единицу', 'price_for_unit'),
            Number::make('Количество', 'quantity'),
            Number::make('Вес', 'weight'),
        ];
    }

    protected function detailFields(): iterable
    {
        return $this->indexFields();
    }
}
