<?php

namespace App\Modules\Product\Attribute\src\Http\Resources;

use Attribute\Http\Resources\AttributeResource;
use Attribute\Models\Attribute;
use Attribute\Models\ProductAttribute;
use Brand\Models\Brand;
use Catalog\Http\Resources\ProductCardResource;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Product\Models\Product;

class ProductAttributeResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = ProductAttribute::class;
    public function toArray($request)
    {
        if ($this->name == 'Мраморность' || $this->slug == "mramornost-miasa"){
            $isFirstAttribute = true;
        }else{
            $isFirstAttribute = false;
        }
        return [
            'id' => $this->id,
            'attribute_id' => $this->id,
            'attribute_name' => $this->name,
            'attribute_slug' => $this->slug,
            'value' => $this->pivot?->value,
            'explanation' => $this->pivot?->explanation,
            'isFirstAttribute' => $isFirstAttribute,
        ];
    }

}
