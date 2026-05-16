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

class CombinationDetailResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */

    public static $model = Combination::class;


    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'name' => $this->translate('name'),
            'description' => $this->translate('description'),
            'cooking_method' => $this->translate('cooking_method'),
            'image' =>  $this->image ? new FileResource($this->image) : null,
            'product' => new ProductCardResource(Product::find($this->product_id)),

            'garnish_title' => $this->translate('garnish_title'),
            'garnishes' => $this->garnishes->map(function ($garnishe) {
                return [
                    'id' => $garnishe->id,
                    'name' => $garnishe->translate('name'),
                    'description' => $garnishe->translate('description'),
                    'image' => $this->image ? new FileResource($garnishe->image) : null,
                    'products' => ProductCardResource::collection(collect($garnishe->products)),
                    ];
            }),

            'sauce_title' => $this->translate('sauce_title'),
            'sauces' => $this->sauces->map(function ($sauce) {
                return [
                    'id' => $sauce->id,
                    'name' => $sauce->translate('name'),
                    'description' => $sauce->translate('description'),
                    'image' => $this->image ? new FileResource($sauce->image) : null,
                    'products' => ProductCardResource::collection(collect($sauce->products)),
                ];
            }),

            'drink_title' => $this->translate('drink_title'),
            'drinks' => $this->drinks->map(function ($drink) {
                return [
                    'id' => $drink->id,
                    'name' => $drink->translate('name'),
                    'description' => $drink->translate('description'),
                    'image' => $this->image ? new FileResource($drink->image) : null,
                ];
            }),

        ];
    }



}
