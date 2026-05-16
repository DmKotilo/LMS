<?php

namespace App\Modules\Combo\src\Http\Resources;

use App\Modules\Cart\src\Enums\CartStatusEnum;
use App\Modules\Cart\src\Traits\GetCart;
use App\Modules\Product\Attribute\src\Http\Resources\ProductAttributeResource;
use App\Modules\Product\Review\src\Http\Resources\ReviewResource;
use App\Modules\Product\Tag\src\Http\Resources\ProductTagResource;
use App\Modules\Product\Tag\src\Http\Resources\TagResource;
use Attribute\Http\Resources\AttributeResource;
use Brand\Http\Resources\BrandResource;
use Cart\Models\UserCart;
use Category\Http\Resources\CategoryResource;
use Category\Models\Category;
use Combo\Models\Combo;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Product\Models\Product;

class ComboCardResource extends JsonResource
{
    use GetCart;
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = Combo::class;
    public function toArray($request)
    {
        $cart = $this->getCart($request);
        $countInCart = $cart->items()
            ->where('item_type', Combo::class)
            ->where('item_id', $this->id)
            ->value('quantity') ?? 0;
        return [
            'id' => $this->id,
            'count_in_cart' =>(int) $countInCart,
            'name' => $this->translate('name'),
            'short_description' => $this->short_description,
            'price' => $this->total_price,
            'quantity' => $this->quantity,
            'sale_price' => $this->sale_price,
            'sale_percent' => $this->getSalePercent(),
            'price_type' => $this->price_type?->toString(),
            'images' => FileResource::collection($this->images),

            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
        ];
    }

}
