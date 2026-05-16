<?php

namespace App\MoonShine\Resources\Order;

use App\Modules\Cart\src\Models\CartItem;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Number;

class CartItemResource extends ModelResource
{
    protected string $model = CartItem::class;
    protected string $title = 'Товар в корзине';

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название товара', 'item_display_name')->readonly(),
            Text::make('Тип товара', 'item_type_display_name')->readonly(),
            Number::make('Количество', 'quantity')->readonly(),
        ];
    }

    protected function detailFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('ID корзины', 'cart_id')->readonly(),
            Text::make('Тип товара', 'item_type_display_name')->readonly(),
            Text::make('Название товара', 'item_display_name')->readonly(),
            Number::make('Количество', 'quantity')->readonly(),
        ];
    }

    protected function rules(mixed $item): array
    {
        return [
            'cart_id'    => ['required', 'exists:user_carts,id'],
            'item_type'  => ['required', 'string'],
            'item_id'    => ['required', 'integer'],
            'quantity'   => ['required', 'integer', 'min:1'],
        ];
    }
}
