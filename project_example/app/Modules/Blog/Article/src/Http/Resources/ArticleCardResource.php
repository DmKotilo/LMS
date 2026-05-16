<?php

namespace App\Modules\Blog\Article\src\Http\Resources;

use App\Modules\Product\Attribute\src\Http\Resources\ProductAttributeResource;
use App\Modules\Product\Review\src\Http\Resources\ReviewResource;
use App\Modules\Product\Tag\src\Http\Resources\ProductTagResource;
use App\Modules\Product\Tag\src\Http\Resources\TagResource;
use Article\Models\Article;
use Attribute\Http\Resources\AttributeResource;
use Banner\Models\Banner;
use Brand\Http\Resources\BrandResource;
use Category\Http\Resources\CategoryResource;
use Category\Models\Category;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Product\Models\Product;

class ArticleCardResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = Article::class;
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'created_at' => \Carbon\Carbon::parse($this->created_at)->translatedFormat('d.m.Y'),
            'updated_at' => \Carbon\Carbon::parse($this->updated_at)->translatedFormat('d.m.Y'),

            'title' => $this->translate('title'),
            'mini_description' => $this->translate('mini_description'),
            'image' => new FileResource($this->image),

            'seo_description' => $this->seo_description,
            'seo_title' => $this->seo_title,
        ];
    }

}
