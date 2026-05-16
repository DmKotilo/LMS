<?php

namespace App\Modules\Cart\src\Http\Resources;


use Combo\Models\Combo;
use File\Http\Resources\FileResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ComboCartResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = Combo::class;
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->translate('name'),
            'article_number' => $this->article_number,
            'description' => $this->translate('description'),
            'price' => round($this->total_price, 2),
            'sale_price' => $this->sale_price,
            'sale_percent' => $this->getSalePercent(),
            'images' => FileResource::collection($this->images),
            'products' => $this->products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->translate('name'),
                    'price' => $this->total_price,
                ];
            }),

        ];
    }

}
