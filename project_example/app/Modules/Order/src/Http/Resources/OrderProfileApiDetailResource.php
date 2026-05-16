<?php

namespace App\Modules\Order\src\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Order\Enums\OrderDeliveryTypeEnum;

class OrderProfileApiDetailResource extends JsonResource
{
    public static $model = \Order\Models\Order::class;

    public function toArray(Request $request): array
    {
        $isCourierDelivery = $this->delivery_type === OrderDeliveryTypeEnum::courier;
        $isPickup = $this->delivery_type === \Order\Enums\OrderDeliveryTypeEnum::pickup;


        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'combo_id' => $this->combo_id,
            'transaction_method' => $this->transactionMethod?->name,
            'transaction_method_description' => $this->transactionMethod?->description,
            'delivery_type' => $this->delivery_type->toString(),
//            'code' => $this->code,
            'promocode' => $this->orderPromocode?->promocode,
            'promocode_percent' => $this->orderPromocode?->percent,

            'comment' => $this->comment,
            'status' => $this->status->toString(),
            'order_price' => (string) $this->price_final,
            'bonus_spent' => (string) $this->bonus_spent,
            'order_weight' => (string) $this->weight,
            'order_item_count' => $this->items->sum('quantity'),
            'created_at' => $this->created_at->format('d.m.Y'),

            'cart_sum' => (string) $this->cart_sum,
            'delivery_price' => (string) $this->delivery_price,
            'personal_discount' => (string) $this->personal_discount,
            'promocode_discount' => (string) $this->promocode_discount,

            'delivery_date' => $this->delivery_date?->format('d.m.Y'),
            'delivery_time_from' => $this->delivery_time_from,
            'delivery_time_to' => $this->delivery_time_to,

            'city' => $isCourierDelivery ? $this->deliveryAddress?->city?->name : null,
            'address_street' => $isCourierDelivery ? $this->deliveryAddress?->street : null,
            'address_house' => $isCourierDelivery ? $this->deliveryAddress?->house : null,

            // Пункт выдачи при самовывозе
            'pickup_title' => $isPickup ? $this->pickupLocation?->title : null,
            'pickup_phone' => $isPickup ? $this->pickupLocation?->phone : null,
            'pickup_city' => $isPickup ? $this->pickupLocation?->city_relation?->name : null,

            'user_fio' => trim($this->last_name . ' ' . $this->name . ' ' . $this->second_name),
            'email' => $this->email,
            'phone' => $this->phone,

            'items' => OrderItemResource::collection($this->items),
        ];
    }
}
