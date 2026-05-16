<?php

namespace App\Modules\Pages\src\Http\Resources;

use App\Modules\Product\Attribute\src\Http\Resources\ProductAttributeResource;
use App\Modules\Product\Review\src\Http\Resources\ReviewResource;
use App\Modules\Product\Tag\src\Http\Resources\ProductTagResource;
use App\Modules\Product\Tag\src\Http\Resources\TagResource;
use Attribute\Http\Resources\AttributeResource;
use Brand\Http\Resources\BrandResource;
use Category\Http\Resources\CategoryResource;
use Category\Models\Category;
use Combo\Models\Combo;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Pages\Models\Reward;
use Product\Models\Product;

class RewardResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = Reward::class;
    public function toArray($request)
    {
        return [
            'name' => $this->name,
            'image' => new FileResource($this->image),
        ];
    }

}
