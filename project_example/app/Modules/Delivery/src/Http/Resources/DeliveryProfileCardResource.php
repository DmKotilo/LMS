<?php

namespace Delivery\Http\Resources;

use Attribute\Models\Attribute;
use Brand\Models\Brand;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Order\Models\Order;
use Product\Models\Product;
use Review\Models\ProductReview;
use User\Models\User;

class DeliveryProfileCardResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = Order::class;
    public function toArray(Request $request): array
    {
        $orderPrice   = $this->cart_sum ?? 0;            // сумма корзины без доставки
        $productCount = $this->items()->sum('quantity');

        return [
            'id' => $this->id,
            'order_number' => $this->code , //todo номер заказа
            'status' => $this->status->toString(),
            'delivery_date' => $this->delivery_date, //todo
            'product_count' => $productCount,
//            'order_price' => $orderPrice,
            'price_final_1c' => $this->price_final_1c,
            'payment_link' => $this->payment_link,
            'order_price' => $this->price_final ?? $this->items->sum('price'),
            'created_at' => \Carbon\Carbon::parse($this->created_at)->translatedFormat('d.m.Y'),
        ];
    }
}
