<?php

namespace App\Modules\Combination\src\Http\Resources;

use App\Modules\Cart\src\Enums\CartStatusEnum;
use App\Modules\Cart\src\Traits\GetCart;
use App\Modules\Product\Attribute\src\Http\Resources\ProductAttributeResource;
use App\Modules\Product\Review\src\Http\Resources\ReviewResource;
use App\Modules\Product\Tag\src\Http\Resources\ProductTagResource;
use App\Modules\Product\Tag\src\Http\Resources\TagResource;
use Attribute\Http\Resources\AttributeResource;
use Brand\Http\Resources\BrandResource;
use Cart\Models\UserCart;
use Catalog\Http\Resources\ProductCardResource;
use Category\Http\Resources\CategoryResource;
use Category\Models\Category;
use Combination\Models\Combination;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Product\Models\Product;

class CombinationCardResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */

    public static $model = Combination::class;

    public function paginationInformation($request, $paginated, $default)
    {
        $default['links']['custom'] = 'https://example.com';

        return $default;
    }

    public function toArray(Request $request): array
    {

        if (!$this->resource) {
            return [];
        }

        return [
            'id' => $this->id,
            'name' => $this->translate('name'),
            'description' => $this->translate('description'),
            'image' => $this->image ?  new FileResource($this->image) : null,

            'seo_description' => $this->seo_description,
            'seo_title' => $this->seo_title,
//            'product' => new ProductCardResource(Product::find($this->product_id)),
        ];
    }



}
