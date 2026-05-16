<?php

namespace App\Modules\Pages\src\Http\Resources;

use App\Interfaces\Interfaces\CartItemPriceable;
use App\Modules\Cart\src\Enums\CartItemTypeEnum;
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
use Pages\Models\Team;
use Product\Models\Product;

class TeamResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = Team::class;
    public function toArray($request)
    {
        return [
            'full_name' => $this->full_name,
            'description' => $this->description,
            'image' => new FileResource($this->image),
        ];
    }





}
