<?php

namespace App\Modules\Notification\src\Http\Resources;

use App\Modules\Promotion\src\Http\Resources\PromotionCardResource;
use Catalog\Http\Resources\ProductCardResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Notification\Models\PromoNotification;
use Product\Models\Product;
use Promotion\Models\Promotion;

class NotificationResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */

    public static $model = PromoNotification::class;


    public function toArray(Request $request): array
    {
        $item = null;
        $buttonLink = null;

//        if ($this->product_id) {
//            $item = new ProductCardResource(Product::find($this->product_id));
//            $buttonLink = route('catalog.product.show', ['id' => $this->product_id]);
//        } elseif ($this->promotion_id) {
//            $item =  new PromotionCardResource(Promotion::find($this->promotion_id));
//            $buttonLink = route('promotion.show', ['promotion' => $this->promotion_id]);
//        }

        if ($this->product_id) {
            $product = Product::find($this->product_id);
            if ($product) {
                $item = new ProductCardResource($product);
                $buttonLink = route('catalog.product.show', $product);
            }
        } elseif ($this->promotion_id) {
            $promotion = Promotion::find($this->promotion_id);
            if ($promotion) {
                $item = new PromotionCardResource($promotion);
                $buttonLink = route('promotion.show', $promotion);
            }
        }

        return [
            'id' => $this->id,
            'type' => $this->type->toString(),
            'item' => $item,
            'button_link' => $buttonLink,
            'is_read' => $this->is_read,
            'created_at' => \Carbon\Carbon::parse($this->created_at)->translatedFormat('d F Y'),
        ];
    }
}
