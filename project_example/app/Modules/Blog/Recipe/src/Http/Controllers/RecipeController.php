<?php

namespace App\Modules\Blog\Recipe\src\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Modules\Blog\Recipe\src\Http\Resources\RecipeCardResource;
use App\Modules\Blog\Recipe\src\Http\Resources\RecipeCategoriesResource;
use App\Modules\Blog\Recipe\src\Http\Resources\RecipeDetailResource;
use App\Modules\Blog\Recipe\src\Http\Resources\RecipeReviewResource;
use App\Modules\Blog\Recipe\src\Http\Resources\RecipeStepResource;
use App\Modules\Blog\Selection\src\Enums\SelectionTypeEnum;
use App\Modules\Review\src\Http\Resources\ReviewResource;
use App\Modules\SeoPage\src\Http\Resources\SeoPageResource;
use Banner\Models\Banner;
use Catalog\Http\Resources\ProductCardResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Recipe\Models\Recipe;
use Recipe\Models\RecipeSelection;
use SeoPage\Models\SeoPage;


#[Group('Страница рецептов', 'API для работы с рецептами')]

class RecipeController extends Controller
{

    #[Endpoint('Список рецептов', 'Получение списка опубликованных рецептов с возможностью поиска и фильтра по категории')]
    #[QueryParam('recipe_search', 'Строка для поиска по title, mini_description, description', required: false, example: 'паста')]
    #[QueryParam('selection_id', 'Массив ID категорий (Selection) для фильтрации', required: false, example: "[2, 3]")]
    #[ResponseFromApiResource(RecipeCardResource::class, Recipe::class,description: 'Список рецептов', collection: true)]
    #[ResponseFromApiResource(RecipeCategoriesResource::class,RecipeSelection::class, description: 'Список категорий', collection: true)]
    public function index(Request $request)
    {
        $query = Recipe::query()->where('publish', true);

        // поиск
        if ($search = $request->input('recipe_search')) {
            $search = mb_strtolower($search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(title) LIKE ?', ["%{$search}%"]);
//                    ->orWhereRaw('LOWER(mini_description) LIKE ?', ["%{$search}%"])
//                    ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"]);
            });
        }

        // фильтр по подборке
        if ($selectionIds = $request->input('selection_id')) {
            $query->whereHas('selections', fn($q) =>
            $q->whereIn('recipe_selections.id', $selectionIds)
            );
        }


        $recipes = $query->get();
        $recipeCategories = RecipeSelection::query()
            ->where('is_displayed', true)
            ->orderBy('order_number', 'ASC')
            ->get();

        //сео данные
        $seoData = SeoPage::where('slug','recipes')->first();
        return Inertia::render('recipe/recipes/ui/RecipesPage', [
            'seoData' => $seoData ? new SeoPageResource($seoData) : null,
            'recipes' => RecipeCardResource::collection($recipes),
            'recipeCategories' => RecipeCategoriesResource::collection($recipeCategories),
            'recipe_search' => $search ?? null,
            'selectedSelectionIds' => $selectionIds ?? [],
        ]);
    }


    public function show(Request $request,Recipe $recipe)
    {
        $recipe->load([
            'products' => fn($query) => $query->active(),
            'variants',
//            'reviews.user',


            'reviews' => function ($query) {
                $query->where('status', \App\Modules\Review\src\Enums\ReviewStatusEnum::Approved)
                    ->with('user');
            },
            'selections',
        ]);


        return Inertia::render('recipe/recipe-detail/ui/RecipeDetailPage', [
            'recipe' => new RecipeDetailResource($recipe),
            'reviews' => ReviewResource::collection($recipe->reviews),
            'reviewsCount' => $recipe->reviews->count(),
            'recipeProducts' => ProductCardResource::collection($recipe->products),
            'recipeProductsCount' => $recipe->products->count(),
            'recipeCategories' => RecipeCategoriesResource::collection(
                $recipe->selections->sortBy(fn($s) => $s->pivot->order_number)
            ),
            'seoData' => new SeoPageResource([
                'page_name' => $recipe->title,
                'seo_title' => $recipe->seo_title,
                'seo_description' => $recipe->seo_description,
                'seo_keywords' => $recipe->keywords,
            ]),
        ]);

//        return [
//            'recipe' => new RecipeDetailResource($recipe),
//            'reviews' => ReviewResource::collection($recipe->reviews),
//            'reviewsCount' => $recipe->reviews->count(),
//            'recipeProducts' => ProductCardResource::collection($recipe->products),
//            'recipeProductsCount' => $recipe->products->count(),
//            'recipeCategories' => RecipeCategoriesResource::collection(
//                $recipe->selections->sortBy(fn($s) => $s->pivot->order_number)
//            ),
//        ];
    }
}
