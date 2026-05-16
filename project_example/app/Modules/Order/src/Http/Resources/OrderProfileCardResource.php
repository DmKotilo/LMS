<?php

namespace App\Modules\Order\src\Http\Resources;

use Attribute\Models\Attribute;
use Brand\Models\Brand;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Order\Models\Order;
use App\Modules\Order\src\Enums\OrderStatusEnum;
use Product\Models\Product;
use Review\Models\ProductReview;
use User\Models\User;

class OrderProfileCardResource extends JsonResource
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
        $productCount = $this->items()->sum('quantity');
        $orderSale    = $this->promocode_discount ?? 0;

        return [
            'id' => $this->id,
            'order_number' => $this->id,
            'status' => $this->status?->toString(),
            'transaction_method' => $this->transactionMethod?->name,
            'product_count' => $productCount,


            'price_final' => $this->price_final,
            'price_final_1с' => $this->price_final_1с,
            'payment_link' => $this->payment_link,
            'can_pay' => $this->status === OrderStatusEnum::AwaitingPayment && !empty($this->payment_link),
            'bonus_spent' => $this->bonus_spent,

            'order_price' => $orderPrice,
            'order_weight' => $orderWeight,
            'delivery_date' => $this->delivery_date?->format('Y-m-d'),
            'delivery_price' => $this->delivery_price,
            'order_sale' => $orderSale,

            'created_at' => \Carbon\Carbon::parse($this->created_at)->translatedFormat('d.m.Y'),
        ];
    }




}
