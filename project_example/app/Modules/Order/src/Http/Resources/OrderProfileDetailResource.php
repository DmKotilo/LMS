<?php

namespace App\Modules\Order\src\Http\Resources;

use App\MoonShine\Resources\User\UserResource;
use App\Modules\Cart\src\Services\CartFormattedItemsService;
use Attribute\Models\Attribute;
use Brand\Models\Brand;
use Carbon\Carbon;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Order\Models\Order;
use App\Modules\Order\src\Enums\OrderStatusEnum;
use Product\Models\Product;
use User\Models\User;
use Order\Enums\OrderDeliveryTypeEnum; // Импортируем enum

class OrderProfileDetailResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */

    public static $model = Order::class;

    public function toArray(Request $request): array
    {
        $orderPrice   = $this->cart_sum ?? 0;
        $orderWeight  = $this->weight ?? 0;

        // Подготовим данные о ПВЗ, если тип доставки - самовывоз
        $pickupLocationData = null;
        $orderCity = null;
        $orderAddress = null;
        $deliveryZoneName = null;
        $deliveryZoneDescription = null;

        if ($this->delivery_type === OrderDeliveryTypeEnum::pickup && $this->relationLoaded('pickupLocation') && $this->pickupLocation !== null) {
            $pickupLocationData = [
                'id' => $this->pickupLocation->id,
                'title' => $this->pickupLocation->title,
                'phone' => $this->pickupLocation->phone,
                'working_hours_from' => $this->pickupLocation->working_hours_from,
                'working_hours_to' => $this->pickupLocation->working_hours_to,
                'site' => $this->pickupLocation->site,
                'full_address' => $this->pickupLocation->full_address, // используем атрибут
                'latitude' => $this->pickupLocation->latitude,
                'longitude' => $this->pickupLocation->longitude,
                // Добавьте другие поля ПВЗ, которые нужны на фронте
            ];

            // Устанавливаем city и address из данных ПВЗ
            $orderCity = $this->pickupLocation->city_dadata; // Или используем связь $this->pickupLocation->city_relation->name, если нужен другой источник
            $orderAddress = $this->pickupLocation->full_address; // Используем атрибут
            // deliveryZoneName и deliveryZoneDescription, скорее всего, не применимы к самовывозу, оставляем null
        } else {
            // Для других типов доставки (например, курьерской) используем данные из deliveryAddress
            $orderCity = $this->deliveryAddress?->city->name;
            $orderAddress = $this->deliveryAddress?->value_dadata;
            $deliveryZoneName = $this->deliveryAddress?->deliveryZone?->name;
            $deliveryZoneDescription = $this->deliveryAddress?->deliveryZone?->description;
        }

        return [
            'id' => $this->id,
            'order_number' => $this->id,
            'status' => $this->status->toString(),
            'order_price' => $orderPrice,
            'comment' => $this->comment,
            'price_final' => $this->price_final,
            'price_final_1c' => $this->price_final_1c,
            'payment_link' => $this->payment_link,
            'can_pay' => $this->status === OrderStatusEnum::AwaitingPayment && !empty($this->payment_link),
            'bonus_spent' => $this->bonus_spent,
            'second_item_discount' => $this->second_item_discount != 0 ? $this->second_item_discount : null,
            'order_weight' => $orderWeight,
            'order_item_count' => $this->items()->sum('quantity'),
            'created_at' => $this->created_at->format('d.m.Y'),
            'order_data' => [
                'city' => $orderCity, // Теперь зависит от типа доставки
                'user_fio' => $this->last_name . ' ' . $this->name . ' ' . $this->second_name,
                'address' => $orderAddress, // Теперь зависит от типа доставки
                'email' => $this->email,
                'phone' => $this->phone,
                'delivery_zone_name' => $deliveryZoneName, // Теперь зависит от типа доставки
                'delivery_zone_description' => $deliveryZoneDescription, // Теперь зависит от типа доставки
                'delivery_type' => $this->delivery_type?->value,
                'pickup_location' => $pickupLocationData, // Добавляем данные ПВЗ, если применимо
            ],
            'delivery_price' => $this->delivery_price,
            'delivery_date' => $this->delivery_date?->format('Y-m-d'),

            'promocode_discount' => $this->promocode_discount,
            'items' => OrderItemResource::collection($this->items),
            'formatedItems' => $this->getFormattedItems(),
        ];
    }

    /**
     * Получить отформатированные элементы заказа с группировкой подарков
     */
    protected function getFormattedItems(): array
    {
        // Убеждаемся, что items загружены с item
        $items = $this->items;
        if (!$this->relationLoaded('items')) {
            $items = $this->items()->with('item')->get();
        }

        if ($items->isEmpty()) {
            return [];
        }

        // Преобразуем OrderItem в формат для CartFormattedItemsService
        $formattedItems = $items->map(function ($orderItem) {
            if (!$orderItem->relationLoaded('item')) {
                $orderItem->load('item');
            }

            $item = $orderItem->item;
            if (!$item) {
                return null;
            }

            $itemResource = OrderItemResource::make($orderItem);
            $itemArray = $itemResource->toArray(request());

            $giftProductIds = [];
            if (!$orderItem->is_gift && $item instanceof Product) {
                $item->loadMissing([
                    'giftProducts' => fn ($query) => $query->where('is_active', true),
                ]);
                $giftProductIds = $item->giftProducts?->pluck('id')->all() ?? [];
            }

            $isFirstOrderGift = false;
            if ($orderItem->is_gift && $item instanceof Product) {
                $isFirstOrderGift = $item->is_first_order_gift ?? false;
            }

            return array_merge($itemArray, [
                'gift_product_ids' => $giftProductIds,
                'is_first_order_gift' => $isFirstOrderGift,
            ]);
        })->filter();

        return CartFormattedItemsService::format(collect($formattedItems));
    }
}
