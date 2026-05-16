<?php

namespace App\Modules\Blog\Recipe\src\Http\Resources;

use App\Modules\Product\Attribute\src\Http\Resources\ProductAttributeResource;
use App\Modules\Product\Review\src\Http\Resources\ReviewResource;
use App\Modules\Product\Tag\src\Http\Resources\ProductTagResource;
use App\Modules\Product\Tag\src\Http\Resources\TagResource;
use Attribute\Http\Resources\AttributeResource;
use Banner\Models\Banner;
use Brand\Http\Resources\BrandResource;
use Category\Http\Resources\CategoryResource;
use Category\Models\Category;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Product\Models\Product;
use Recipe\Models\Recipe;
use Recipe\Models\RecipeSelection;


class RecipeCategoriesResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = RecipeSelection::class;
    public function toArray(Request $request): array
    {


        return [
            'id' => (string) $this->id,
            'title' => $this->translate('title'),
            'order_number' => (int) $this->order_number,
        ];
    }

}
