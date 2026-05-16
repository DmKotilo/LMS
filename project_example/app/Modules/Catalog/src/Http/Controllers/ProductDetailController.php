<?php

namespace App\Modules\Catalog\src\Http\Controllers;

use Address\Http\Resources\AddressResource;
use App\Http\Controllers\Controller;
use App\Modules\Banner\src\Http\Enums\BannerTypeEnum;
use App\Modules\Banner\src\Http\Resources\BannerResource;
use App\Modules\Blog\Recipe\src\Http\Resources\RecipeCardResource;
use App\Modules\City\src\Http\Resources\CityResource;
use App\Modules\Combination\src\Http\Resources\CombinationCardResource;
use App\Modules\Product\Review\src\Http\Resources\ReviewResource;
use Banner\Models\Banner;
use Catalog\Http\Resources\ProductCardResource;
use Catalog\Http\Resources\ProductDetailResource;
use City\Models\City;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\UrlParam;
use Order\Models\Order;
use Order\Models\OrderItem;
use Order\Models\OrderProduct;
use Product\Models\Product;
use Recipe\Models\Recipe;
use Category\Models\Category;

#[Group('Каталог', 'Методы для получения информации о продуктах')]

class ProductDetailController extends Controller
{
//    #[Endpoint('Получить продукт по ID', 'Возвращает детальную информацию о продукте. routeName:catalog.product.show')]
//    #[UrlParam("id", "int", 'ID продукта', required: true, example: 13)]
//    #[ResponseFromApiResource(ProductDetailResource::class, Product::class)]
    public function show(Request $request, Product $product)
    {
        if (!$product->isActive()) {
            abort(404);
        }

        // Параметры сортировки отзывов
        $allowedSortFields = [
            'created_at',
            'rating',
            'usefulness', // по полезности (likes_count)
        ];

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');

        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'created_at';
        }

        if (!in_array(strtolower($sortDir), ['asc', 'desc'])) {
            $sortDir = 'desc';
        }

        // Загружаем отзывы с учетом сортировки
        $reviewsQuery = $product->reviews()
            ->where('status', \App\Modules\Review\src\Enums\ReviewStatusEnum::Approved)
            ->with(['user', 'answer']);

        // Сортировка по полезности (количество лайков)
        if ($sortBy === 'usefulness') {
            $reviewsQuery->withCount([
                'reactions as likes_count' => function ($query) {
                    $query->where('type', 'like');
                }
            ])->orderBy('likes_count', $sortDir);
        } else {
            // Сортировка по дате или оценке
            $reviewsQuery->orderBy($sortBy === 'created_at' ? 'created_at' : 'rating', $sortDir);
        }

        // Загружаем отзывы с сортировкой
        $product->setRelation('reviews', $reviewsQuery->get());

        $product->load([
            'images',
            'categories.parent.parent.parent.parent', // Загружаем до 4 уровней родителей для подъема по дереву
            'cutting',
            'manufacturer',
            'tags',
            'attributes_values.attribute',
            'brands',
            'videos', // Загружаем видео
            'videos.previewOf.preview', // video (File) -> previewOf (VideoPreview) -> preview (File)
            'combinations',
            'giftProducts.images',
            'giftProducts.tags',
            'giftProducts.brands',
        ]);

        // Логика выбора категории на основе query параметра category_id
        $selectedCategory = null;
        $rootCategory = null;
        $categoryIdFromQuery = $request->input('category_id');
        
        if ($categoryIdFromQuery) {
            // Приводим category_id к int для корректного сравнения
            $categoryIdFromQuery = (int) $categoryIdFromQuery;
            
            // Сначала проверяем, есть ли у продукта категория с id = category_id из query
            $directMatch = $product->categories->first(function ($category) use ($categoryIdFromQuery) {
                return $category->id == $categoryIdFromQuery;
            });
            
            if ($directMatch) {
                // Если нашли прямую категорию, используем ее
                $selectedCategory = $directMatch;
            } else {
                // Ищем категорию продукта, у которой родительская категория = category_id из query
                $parentMatch = $product->categories->first(function ($category) use ($categoryIdFromQuery) {
                    return $category->parent_category_id == $categoryIdFromQuery;
                });
                
                if ($parentMatch) {
                    $selectedCategory = $parentMatch;
                } else {
                    // Ищем категорию продукта, у которой корневая категория = category_id из query
                    foreach ($product->categories as $category) {
                        // Находим корневую категорию для текущей категории
                        $current = $category;
                        // Поднимаемся вверх по дереву до корневой категории
                        while ($current && $current->parent) {
                            $current = $current->parent;
                        }
                        $rootOfCategory = $current ?: $category;
                        
                        // Проверяем, равна ли корневая категория category_id из query
                        if ($rootOfCategory->id == $categoryIdFromQuery) {
                            $selectedCategory = $category;
                            break;
                        }
                    }
                }
            }
        }
        
        // Если не нашли по query параметру, берем первую категорию продукта
        if (!$selectedCategory && $product->categories->isNotEmpty()) {
            $selectedCategory = $product->categories->first();
        }
        
        // Находим корневую категорию для выбранной категории
        if ($selectedCategory) {
            $current = $selectedCategory;
            while ($current && $current->parent) {
                $current = $current->parent;
            }
            $rootCategory = $current ?: $selectedCategory;
        }
        
        // Сохраняем выбранную корневую категорию в request для использования в ProductDetailResource
        if ($rootCategory) {
            // Перезагружаем корневую категорию с полным деревом из базы данных
            $rootCategory = Category::with(['children' => function ($query) {
                $query->with(['children' => function ($query) {
                    $query->with(['children' => function ($query) {
                        $query->with('children'); // Загружаем до 4 уровней вложенности
                    }]);
                }]);
            }])->find($rootCategory->id);
            
            if ($rootCategory) {
                $request->merge(['selected_root_category_id' => $rootCategory->id]);
            }
        }

//        $banners = Banner::where('banner_type',BannerTypeEnum::ProductDetail)->where('is_active',true)->get();

        $banners = Banner::where('is_active', true)
            ->where('banner_type', BannerTypeEnum::ProductDetail)
            ->orderBy('created_at', 'ASC')
            ->get();

        $recommendationLimit = (int) config('recommendations.auto_related.limit', 10);
        $recommendationRelations = [
            'images',
            'categories',
            'tags',
            'attributes_values.attribute',
            'brands',
            'reviews.user',
            'reviews.answer',
        ];

        $recomendedProducts = $product->relatedProducts()
            ->with($recommendationRelations)
            ->active()
            ->limit($recommendationLimit)
            ->get();

        if ($recomendedProducts->isEmpty()) {
            $recomendedProducts = $product->autoRelatedProducts()
                ->with($recommendationRelations)
                ->active()
                ->orderByDesc('auto_related_products.confidence')
                ->limit($recommendationLimit)
                ->get();
        }

        $recomendedRecipes = Recipe::whereHas('products', function ($query) use ($product) { // <--- Используем $product
            $query->where('product_id', $product->id); // <--- Используем $product->id
        })->get();

        $userPurchasedProducts = collect();
        if (auth()->check()) {
            $userOrderIds = Order::where('user_id', auth()->id())->pluck('id');

            $userPurchasedProductIds = OrderItem::whereIn('order_id', $userOrderIds)
                ->where('item_type', Product::class)
                ->pluck('item_id')
                ->unique();

            $userPurchasedProducts = Product::with([
                'images',
                'categories',
                'tags',
            ])
                ->whereIn('id', $userPurchasedProductIds)
                ->active()
                ->get();
        }

        $combinations = $product->combinations()
        ->where('is_active', true)
        ->with(['image'])->get();
        
        // Устанавливаем выбранную корневую категорию в relation продукта
        if ($rootCategory) {
            $product->setRelation('selectedRootCategory', $rootCategory);
        }
        
        return Inertia::render('product/ui/ProductPage', [
            'product' => new ProductDetailResource($product),
            'banners' => $banners ? BannerResource::collection($banners) : [],
            'recommended_products' => ProductCardResource::collection($recomendedProducts),
            'recomended_recipes' => RecipeCardResource::collection($recomendedRecipes),
            'user_purchased_products' => ProductCardResource::collection($userPurchasedProducts),
            'cities_zones' => CityResource::collection(City::active()->ordered()->get()),
            'userAddresses' => auth()->user() ? AddressResource::collection(auth()->user()->addresses) : null,
            'combinations' => CombinationCardResource::collection($combinations),

        ]);
//        return [
//            'product' => new ProductDetailResource($product),
//            'banners' => $banners ? BannerResource::collection($banners) : [],
//            'recommended_products' => ProductCardResource::collection($recomendedProducts),
//            'recomended_recipes' => RecipeCardResource::collection($recomendedRecipes),
//            'user_purchased_products' => ProductCardResource::collection($userPurchasedProducts),
//            'cities_zones' => CityResource::collection(City::active()->get()),
//            'userAddresses' => auth()->user() ? AddressResource::collection(auth()->user()->addresses) : null,
//        ];

    }
}
