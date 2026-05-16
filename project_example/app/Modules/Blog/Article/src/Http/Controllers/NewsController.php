<?php

namespace App\Modules\Blog\Article\src\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Banner\src\Http\Enums\BannerTypeEnum;
use App\Modules\Banner\src\Http\Resources\BannerResource;
use App\Modules\Blog\Article\src\Http\Resources\ArticleCardResource;
use App\Modules\Blog\Article\src\Http\Resources\ArticleDetailResource;
use App\Modules\Blog\Article\src\Http\Resources\ArticleSelectionDetailResource;
use App\Modules\Blog\Article\src\Http\Resources\ArticleSelectionResource;
use App\Modules\SeoPage\src\Http\Resources\SeoPageResource;
use Article\Models\Article;
use Article\Models\ArticleSelection;
use Banner\Models\Banner;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use SeoPage\Models\SeoPage;

#[Group('Блог новостей')]
class NewsController extends Controller
{
    #[Endpoint("Список подборок статей", "Получение списка подборок с возможностью поиска и сортировки.")]
    #[QueryParam("sort", required: false, example: "newest", description: "Способ сортировки:
    - `order` (по порядковому номеру (если сортировка не передана)),
    - `newest` (по дате создания, сначала новые),
    - `popular` (по количеству просмотров, сначала самые популярные).")]
    #[QueryParam("search", required: false, example: "стейк", description: "Поисковый запрос. Поиск ведётся по названию и описанию.(Поиск выдает и подборки и статьи)")]
    #[ResponseFromApiResource(ArticleSelectionResource::class,ArticleSelection::class, description: 'Список категорий', collection: true)]

    public function index(Request $request)
    {
        $sort = $request->get('sort'); // order | newest | popular
        $search = $request->get('blog_search');

        $query = ArticleSelection::query()
            ->where('is_displayed', true)
            ->where('is_main', false)
            ->where('is_popular', false)
            ->whereHas('articles', fn ($q) => $q->where('publish', true))
            ->withCount(['articles' => fn ($q) => $q->where('publish', true)]);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(title) LIKE ?', ['%' . mb_strtolower($search) . '%'])
                    ->orWhereRaw('LOWER(description) LIKE ?', ['%' . mb_strtolower($search) . '%']);
            });
        }

        if ($sort === 'newest') {
            $query->orderBy('created_at', 'desc');
        } elseif ($sort === 'popular') {
            $query->orderBy('views_count', 'desc');
        } elseif ($sort === 'order') {
            $query->orderBy('order_number', 'asc');
        }
        $selections = $query->get();

        $articles = collect();
        if (!empty($search)) {
            $articlesQuery = Article::where('publish', true)
                ->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(title) LIKE ?', ['%' . mb_strtolower($search) . '%'])
                        ->orWhereRaw('LOWER(description) LIKE ?', ['%' . mb_strtolower($search) . '%'])
                        ->orWhereRaw('LOWER(text) LIKE ?', ['%' . mb_strtolower($search) . '%']);
                });

            $articles = $articlesQuery->get();
        }

        $popularArticle = Article::where('publish', true)
            ->where('is_popular', true)
            ->get();

        $popularSelections = ArticleSelection::query()
            ->where('is_popular', true)
            ->where('is_displayed', true)
            ->whereHas('articles', fn ($q) => $q->where('publish', true))
            ->withCount(['articles' => fn ($q) => $q->where('publish', true)])
            ->get();

        //сео данные
        $seoData = SeoPage::where('slug', 'news')->first();

        $mainSelection = ArticleSelection::query()
            ->where('is_main', true)
            ->whereHas('articles', fn ($q) => $q->where('publish', true))
            ->withCount(['articles' => fn ($q) => $q->where('publish', true)])
            ->first();
        return Inertia::render('blog/blog-page/ui/BlogPage', [
            'seoData' => $seoData ? new SeoPageResource($seoData) : null,
            'selections' => $selections ? ArticleSelectionResource::collection($selections) : null,
            'mainSelection' => $mainSelection
                ? new ArticleSelectionResource($mainSelection)
                : null,
            'articles' => ArticleCardResource::collection($articles),
            'popular_article' => ArticleCardResource::collection($popularArticle),
            'popular_selections' => ArticleSelectionResource::collection($popularSelections),
        ]);
    }



    #[Endpoint('Страница подборки статей', 'Страница подборки с её статьями.')]
    #[QueryParam('search', required: false, example: 'стейк', description: 'Поисковый запрос по статьям подборки. Поиск ведётся по title, description и text, без учёта регистра.')]
    #[ResponseFromApiResource(ArticleSelectionDetailResource::class,ArticleSelection::class, description: 'Список статей в подборке', collection: true)]

    public function selectionDetail(Request $request, ArticleSelection $selection)
    {
        $selection->increment('views_count');

        $search = $request->get('article_search');

        $selection->load([
            'articles' => function ($q) use ($search) {
                $q->where('publish', true);

                if (!empty($search)) {
                    $q->where(function ($query) use ($search) {
                        $query->whereRaw('LOWER(title) LIKE ?', ['%' . mb_strtolower($search) . '%'])
                            ->orWhereRaw('LOWER(description) LIKE ?', ['%' . mb_strtolower($search) . '%'])
                            ->orWhereRaw('LOWER(text) LIKE ?', ['%' . mb_strtolower($search) . '%']);
                    });
                }
            },
            'image'
        ])->loadCount('articles');

        return Inertia::render('blog/blog-category/ui/BlogCategory', [
            'selection' => new ArticleSelectionDetailResource($selection),
        ]);
    }
    #[Endpoint('Страница статьи', 'Детальная страница статьи.')]
    #[ResponseFromApiResource(ArticleDetailResource::class,Article::class, description: 'Статья', collection: true)]
    #[ResponseFromApiResource(BannerResource::class,Banner::class, description: 'Баннер внизу страницы', collection: true)]
    public function articleDetail(Request $request, Article $article)
    {
        $article->load([
            'image',
            'selections',
            'relatedArticles.related',
            // 'reviews.user',
            // 'reviews.answer',
            'approvedComments.user',
            'selections',
        ]);
        $banners = Banner::where('is_active', true)
            ->where('banner_type', BannerTypeEnum::ArticleDetail)
            ->orderBy('created_at', 'ASC')
            ->get();


        return Inertia::render('blog/blog-article/ui/BlogArticlePage',[
            'article' => new ArticleDetailResource($article),
            'banners' => BannerResource::collection($banners),
        ]);
    }

}
