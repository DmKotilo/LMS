<?php

namespace App\Modules\Cart\src\Http\Resources;

use App\Modules\Product\Attribute\src\Http\Resources\ProductAttributeResource;
use Brand\Http\Resources\BrandResource;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Product\Models\Product;

class ProductCartResource extends JsonResource
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
            'degree_type' => $this->degree_type?->toString(),
            'name' => $this->translate('name'),
            'article_number' => $this->article_number,
            'weight' => ($this->is_weight_show && $this->weight > 0) ? round($this->weight, 2) : null,
            'weight_type' => ($this->is_weight_show && $this->weight > 0) ? $this->weight_type?->toString() : null,
            'is_weight_show' => $this->is_weight_show,
            'price' => round($this->base_price, 2),
            'price_type' => $this->price_type?->toString(),
            'sale_price' => $this->sale_price,
            'cashback_percent' => $this->getCashbackPercent(),
            'images' => FileResource::collection($this->images),
            'attributes' => ProductAttributeResource::collection($this->attributes_values),
            'brands' => BrandResource::collection($this->brands),
            'slug' => $this->slug,

        ];
    }


}
