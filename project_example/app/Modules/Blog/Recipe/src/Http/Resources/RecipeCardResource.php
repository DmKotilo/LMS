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

class RecipeCardResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = Recipe::class;
    public function toArray(Request $request): array
    {
        $user = auth()->user();
        $isInWishlist = false;

        if ($user && $user->whiteList) {
            $isInWishlist = $user->whiteList->items()
                ->where('item_type', Recipe::class)
                ->where('item_id', $this->id)
                ->exists();
        }

        return [
            'id' => $this->id,
            'title' => $this->translate('title'),
            'mini_description' => $this->translate('mini_description'),
            'cooking_time_minutes'  => $this->cooking_time_minutes,
            'difficult'  => $this->difficult,
            'number_of_persons'  => $this->number_of_persons,
            'is_wishlist' => $isInWishlist,
            'image' => new FileResource($this->firstImage),
            'content'=>$this->content,
            'active_cooking_time_minutes'=>$this->active_cooking_time_minutes,
            'rating' => $this->rating,

            'seo_description' => $this->seo_description,
            'seo_title' => $this->seo_title,
        ];
    }

}
