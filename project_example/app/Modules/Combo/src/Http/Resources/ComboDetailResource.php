Ф<?php

namespace App\Modules\Combo\src\Http\Resources;

use App\Modules\Cart\src\Traits\GetCart;
use App\Modules\Review\src\Enums\ReviewStatusEnum;
use App\Modules\Review\src\Http\Resources\ReviewResource;
use Catalog\Http\Resources\ProductCardResource;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Product\Models\Product;

class ComboDetailResource extends JsonResource
{
    use GetCart;

    public function toArray(Request $request): array
    {
        $cart = $this->getCart($request);
        $countInCart = $cart->items()
            ->where('item_type', \Combo\Models\Combo::class)
            ->where('item_id', $this->id)
            ->value('quantity') ?? 0;

        $approvedReviews = $this->whenLoaded('reviews', fn() => $this->reviews->where('status', ReviewStatusEnum::Approved), collect());


        return [
            'id' => $this->id,
            'count_in_cart' => (int) $countInCart,
            'name' => html_entity_decode($this->translate('name'), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'short_description' => $this->short_description,
            'description' => $this->translate('description'),
            'price' => $this->total_price,
            'sale_price' => $this->sale_price,
            'sale_percent' => $this->getSalePercent(),
            'price_type' => $this->price_type?->toString(),
            'article_number' => $this->article_number,
            'quantity' => $this->quantity,
            'weight' => $this->weight > 0 ? $this->weight : null,
            'weight_type' => $this->weight > 0 ? $this->weight_type?->toString() : null,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'images' => FileResource::collection($this->images),
            'first_image' => new FileResource($this->firstImage),
            'reviews_count' => $approvedReviews->count(),
            'average_rating' => $this->rating,
            'reviews' => ReviewResource::collection($approvedReviews),
            'products' => ProductCardResource::collection($this->products),
        ];
    }
}
