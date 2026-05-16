<?php

namespace App\Modules\Blog\Recipe\src\Http\Resources;

use App\Modules\Product\Attribute\src\Http\Resources\ProductAttributeResource;
use App\Modules\Product\Review\src\Http\Resources\ReviewResource;
use App\Modules\Product\Tag\src\Http\Resources\ProductTagResource;
use App\Modules\Product\Tag\src\Http\Resources\TagResource;
use Attribute\Http\Resources\AttributeResource;
use Banner\Models\Banner;
use Brand\Http\Resources\BrandResource;
use Carbon\Carbon;
use Category\Http\Resources\CategoryResource;
use Category\Models\Category;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Product\Models\Product;
use Recipe\Models\Recipe;

class RecipeDetailResource extends JsonResource
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
            'description' => $this->translate('description'),
            'mini_description' => $this->translate('mini_description'),
            'cooking_time_minutes'  => $this->cooking_time_minutes,
            'active_cooking_time_minutes'  => $this->active_cooking_time_minutes,
            'steps' => collect($this->steps)->map(function ($step) {
                return [
                    'title' => $step['title'],
                    'description' => $step['description'],
                    'images' => $step['images']
                        ? asset('storage/'.$step['images'])
                        : null,
                ];
            }),


            'difficult'  => $this->difficult,
            'number_of_persons'  => $this->number_of_persons,
            'is_wishlist' => $isInWishlist,
            'ingredients_for_dish_json' => $this->ingredients_for_dish_json,
            'ingredients_for_sauce_json' => $this->ingredients_for_sauce_json,
            'images' => FileResource::collection($this->images),
            'videos' => FileResource::collection($this->videos),
            'created_at' => Carbon::parse($this->created_at)->translatedFormat('j F Y'),
            'content'=>$this->content,
            'rating' => $this->rating,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'seo_keywords' => $this->keywords,
        ];
    }

}
