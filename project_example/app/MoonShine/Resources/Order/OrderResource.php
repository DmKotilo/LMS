<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Order;

use App\Modules\Order\src\Enums\OrderStatusEnum;
use App\MoonShine\Resources\Delivery\DeliveryZoneResource;
use App\MoonShine\Resources\User\UserResource;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Laravel\Enums\Action;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Json;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Phone;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use Order\Models\Order;

use MoonShine\Support\ListOf;



/**
 * @extends ModelResource<Order>
 */
class OrderResource extends ModelResource
{
    protected string $model = Order::class;

    protected string $title = 'Заказы';

    protected function search(): array
    {
        return [
            'user.name',
            'user.second_name',
            'user.last_name',
            'user.email',
            'user.phone',
            'phone'
        ];
    }


    protected function activeActions(): ListOf
    {
        return parent::activeActions()
            ->only(Action::VIEW, Action::UPDATE); // оставляем только просмотр
    }

    protected function filters(): iterable
    {
        return [
            Text::make('Bitrix Lead ID', 'bitrix_lead_id')
                ->nullable()
                ->default(null)
                ->onApply(function($query, $value, $field) {
                    if (!is_null($value) && $value !== '') {
                        $query->where('bitrix_lead_id', 'like', "%{$value}%");
                    }
                    return $query;
                }),
            Text::make('Bitrix Deal ID', 'bitrix_deal_id')
                ->nullable()
                ->default(null)
                ->onApply(function($query, $value, $field) {
                    if (!is_null($value) && $value !== '') {
                        $query->where('bitrix_deal_id', 'like', "%{$value}%");
                    }
                    return $query;
                }),
        ];
    }

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Клиент', 'user', 'name', UserResource::class),
            Number::make('Финальная цена', 'price_final'),
            Number::make('Финальная цена 1C', 'price_final_1c'),
            Enum::make('Статус', 'status')->attach(OrderStatusEnum::class),

        ];
    }

    protected function formFields(): iterable
    {
        return [
            Box::make([
                Tabs::make([
//                    Tab::make('Данные клиента', [
//                        BelongsTo::make('Клиент', 'user', 'name', UserResource::class),
//                        Flex::make([
//                            Text::make('Имя', 'name'),
//                            Text::make('Отчество', 'second_name'),
//                            Text::make('Фамилия', 'last_name'),
//                        ]),
//                        Text::make('E-mail', 'email'),
//                        Flex::make([
////                            Text::make('Город', 'address_city'),
////                            Text::make('Улица', 'address_street'),
////                            Text::make('Дом', 'address_house'),
//                        ]),
//                    ]),
                    Tab::make('Данные заказа', [
                        ID::make(),
                        Enum::make('Статус', 'status')->attach(OrderStatusEnum::class),
//                        Number::make('Быстрая покупка товара', 'product'),
//                        Flex::make([
//                            Text::make('Промокод', 'promo'),
////                            Text::make('Код заказа', 'code'),
//                        ]),
//                        Textarea::make('Комментарий', 'comment'),
//                        Box::make('Финансовая информация', [
//                            Flex::make([
//                                Number::make('Сумма корзины', 'cart_sum'),
//                                Number::make('Цена доставки', 'delivery_price'),
//                                Number::make('Персональная скидка', 'personal_discount'),
//                                Number::make('Скидка по промокоду', 'promocode_discount'),
//                                Number::make('Финальная цена', 'price_final'),
//                                Number::make('Финальная цена 1С', 'price_final_1c'),
//                            ]),
//                        ]),
//                        Box::make('Дополнительные данные', [
//                            Flex::make([
//                                Number::make('Вес заказа', 'order_weight'),
//                                Number::make('Количество', 'quantity'),
//                            ]),
//                        ]),
                    ]),
                    Tab::make('Для разработчиков', [
                        Text::make('UUID 1C', 'uuid_1c')->readonly(),
                        Number::make('Финальная цена 1C', 'price_final_1c')->readonly(),
                        Text::make('Bitrix Lead ID', 'bitrix_lead_id')->readonly(),
                        Text::make('Bitrix Deal ID', 'bitrix_deal_id')->readonly(),
                        Text::make('Ссылка на оплату', 'payment_link')->readonly(),
                    ]),
                ]),
            ]),

        ];
    }

    protected function detailFields(): iterable
    {
        return [
            ID::make(),
            BelongsTo::make('Клиент', 'user', 'name', UserResource::class),
            Number::make('Быстрая покупка товара', 'product'),
            Text::make('Имя', 'name'),
            Text::make('Отчество', 'second_name'),
            Text::make('Фамилия', 'last_name'),
            Text::make('E-mail', 'email'),
            Phone::make(
                'Телефон',
                'phone',
                static function (?Order $order): ?string {
                    $value = $order?->phone;

                    if ($value === null || $value === '') {
                        return $value;
                    }

                    $normalized = trim((string) $value);

                    if (str_starts_with($normalized, '+7')) {
                        return $normalized;
                    }

                    $normalized = ltrim($normalized, '+');

                    return '+7' . $normalized;
                }
            ),

//            Text::make('Город', 'address_city'),
//            Text::make('Улица', 'address_street'),
//            Text::make('Дом', 'address_house'),


            Text::make('Тип доставки', 'delivery_type_label')->readonly(),

            Text::make('Адрес', 'delivery_address_for_admin')
                ->readonly(),




            Text::make('Промокод', 'promo'),
            Text::make('Код заказа', 'code'),

            Textarea::make('Комментарий', 'comment'),
            Date::make('Дата доставки', 'delivery_date'),
            Json::make('Временной интервал доставки', 'delivery_time_interval'),

            Number::make('Сумма корзины', 'cart_sum'),
            Number::make('Цена доставки', 'delivery_price'),
            Number::make('Персональная скидка', 'personal_discount'),
            Number::make('Скидка по промокоду', 'promocode_discount'),
            BelongsTo::make('Промокод', 'orderPromocode', 'promocode', OrderPromocodeResource::class),

            Number::make('Финальная цена', 'price_final'),
            Number::make('Финальная цена 1С', 'price_final_1c'),
            Text::make('Ссылка на оплату', 'payment_link')->readonly(),


            Number::make('Вес заказа', 'order_weight'),
            Number::make('Количество', 'quantity'),

            Enum::make('Статус', 'status')->attach(OrderStatusEnum::class),
            HasMany::make('Товары', 'items', 'name',OrderItemResource::class)


        ];
    }



    protected function rules(mixed $item): array
    {
        return [];
    }


}
