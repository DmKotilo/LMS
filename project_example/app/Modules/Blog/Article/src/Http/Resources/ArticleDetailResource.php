<?php

namespace App\Modules\Blog\Article\src\Http\Resources;

use App\Modules\Product\Attribute\src\Http\Resources\ProductAttributeResource;
use App\Modules\Product\Review\src\Http\Resources\ReviewResource;
use App\Modules\Product\Tag\src\Http\Resources\ProductTagResource;
use App\Modules\Product\Tag\src\Http\Resources\TagResource;
use App\Modules\Review\src\Enums\ReviewStatusEnum;
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

use App\Modules\Comment\src\Http\Resources\CommentResource;
use App\Modules\Blog\Article\src\Http\Resources\ArticleCardResource;

class ArticleDetailResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = Article::class;
    protected function parseAuthorName(?string $author): array
    {
        $name = trim($author ?? '');
        if ($name === '') {
            return [
                'first_name' => '',
                'last_name' => '',
            ];
        }

        $parts = explode(' ', $name, 2);
        $firstName = $parts[0] ?? '';
        $lastName = $parts[1] ?? '';

        return [
            'first_name' => $firstName,
            'last_name' => $lastName
        ];
    }
    public function toArray(Request $request): array
    {
//        $approvedReviews = $this->reviews->where('status', ReviewStatusEnum::Approved);
        $nextArticle = Article::query()
            ->where('publish', true)
            ->where('id', '>', $this->id)
            ->orderBy('id')
            ->first();

        $previousArticle = Article::query()
            ->where('publish', true)
            ->where('id', '<', $this->id)
            ->orderByDesc('id')
            ->first();

        $authorFullName = $this->author ?? '';
        $parsedAuthor = $this->parseAuthorName($authorFullName);

        return [
            'id' => $this->id,
//            'author' => $this->author,
            'author' => [
                'full_name' => $authorFullName,
                'first_name' => $parsedAuthor['first_name'],
                'last_name' => $parsedAuthor['last_name'],
            ],

            'created_at' => \Carbon\Carbon::parse($this->created_at)->translatedFormat('d F Y'),
            'updated_at' => \Carbon\Carbon::parse($this->updated_at)->translatedFormat('d.m.Y'),
            'title' => $this->translate('title'),
            'description' => $this->translate('description'),
            'text' => $this->translate('text'),
            // Если у статьи нет картинки, отдаем null вместо ошибки обращения к null
            'image' => $this->when(
                $this->image,
                fn () => new FileResource($this->image),
                null
            ),
//            'reviews_count' => $approvedReviews->count(),
//            'reviews' => \App\Modules\Review\src\Http\Resources\ReviewResource::collection($approvedReviews),
            'comments_count' => $this->whenLoaded('approvedComments', fn() => $this->approvedComments->count(), 0),
            'comments' => CommentResource::collection($this->whenLoaded('approvedComments')),
            'tags' => $this->tags
                ? array_map('trim', explode(',', $this->tags))
                : [],
            'related' => ArticleCardResource::collection(
                $this->relatedArticles->map->related
            ),
            'seo_description' => $this->seo_description,
            'seo_title' => $this->seo_title,
            'next_url' => $nextArticle ? route('blog.article.show', ['article' => $nextArticle]) : null,
            'before_url' => $previousArticle ? route('blog.article.show', ['article' => $previousArticle]) : null,
            'selection' => [
                'title' => $this->selections[0]?->title ?? null,
                'id' => $this->selections[0]?->id ?? null
            ],

        ];
    }

}
