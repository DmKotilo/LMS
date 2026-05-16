<?php

namespace App\Modules\Blog\Article\src\Http\Resources;

use App\Modules\Product\Attribute\src\Http\Resources\ProductAttributeResource;
use App\Modules\Product\Review\src\Http\Resources\ReviewResource;
use App\Modules\Product\Tag\src\Http\Resources\ProductTagResource;
use App\Modules\Product\Tag\src\Http\Resources\TagResource;
use Article\Models\ArticleSelection;
use Attribute\Http\Resources\AttributeResource;
use Banner\Models\Banner;
use Brand\Http\Resources\BrandResource;
use Category\Http\Resources\CategoryResource;
use Category\Models\Category;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Product\Models\Product;

class ArticleSelectionDetailResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = ArticleSelection::class;
    public function toArray(Request $request): array
    {
        $articles = $this->relationLoaded('articles')
            ? $this->articles
            : $this->articles()->where('publish', true)->get();

        return [
            'image' => new FileResource($this->image),
            'id' => $this->id,
            'created_at' => \Carbon\Carbon::parse($this->created_at)->translatedFormat('d.m.Y'),
            'updated_at' => \Carbon\Carbon::parse($this->updated_at)->translatedFormat('d.m.Y'),
            'title' => $this->translate('title'),
            'description' => $this->translate('description'),
            'order_number' => $this->order_number,
            'is_main' => $this->is_main,
            'article_count'=> $articles->count(),
            'articles'=> ArticleCardResource::collection($articles),
        ];
    }

}
