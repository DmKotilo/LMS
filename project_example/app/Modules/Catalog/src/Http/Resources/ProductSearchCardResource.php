<?php

namespace Catalog\Http\Resources;

use App\Modules\Cart\src\Enums\CartStatusEnum;
use App\Modules\Product\Attribute\src\Http\Resources\ProductAttributeResource;
use App\Modules\Product\Review\src\Http\Resources\ReviewResource;
use App\Modules\Product\Tag\src\Http\Resources\ProductTagResource;
use App\Modules\Product\Tag\src\Http\Resources\TagResource;
use Attribute\Http\Resources\AttributeResource;
use Brand\Http\Resources\BrandResource;
use Cart\Models\UserCart;
use Category\Http\Resources\CategoryResource;
use Category\Models\Category;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Product\Models\Product;

class ProductSearchCardResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = Product::class;
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'name' => html_entity_decode($this->translate('name'), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'price' => $this->base_price,
            'slug' => $this->slug,
            'price_type' => $this->price_type?->toString(),
            'sale_price' => $this->base_sale_price,
            'sale_percent' => $this->getSalePercent(),
            'images' => $this->getDisplayImages(),
            ];
    }


}
