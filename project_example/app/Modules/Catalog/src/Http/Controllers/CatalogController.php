<?php

namespace Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Combination\src\Http\Resources\CombinationCardResource;
use App\Modules\Product\Product\src\Enums\PageSlugEnum;
use App\Modules\Product\Product\src\Models\RecomendedProductPage;
use App\Modules\Product\Tag\src\Http\Resources\TagResource;
use App\Modules\Review\src\Enums\ReviewStatusEnum;
use App\Modules\Review\src\Http\Resources\ReviewHomeCardResource;
use App\Modules\SeoPage\src\Http\Resources\SeoPageResource;
use Brand\Http\Resources\BrandResource;
use Manufacturer\Http\Resources\ManufacturerShortResource;
use Catalog\Http\Resources\ProductCardCollection;
use Catalog\Http\Resources\ProductCardResource;
use Category\Http\Resources\CategoryDetailResource;
use Category\Http\Resources\CategoryItemTreeResource;
use Category\Http\Resources\CategoryResource;
use Category\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Product\Models\Product;
use Review\Models\ProductReview;
use Review\Models\Review;
use SeoPage\Models\SeoPage;
use Setting\Models\Setting;

class CatalogController extends Controller
{
    protected ?int $secondItemDiscountPercentCache = null;

    protected function getSecondItemDiscountPercentValue(): int
    {
        if (is_null($this->secondItemDiscountPercentCache)) {
            $value = Setting::where('key', 'second_item_discount_percent')->value('value');
            $this->secondItemDiscountPercentCache = is_null($value) ? 10 : (int) $value;
        }

        return $this->secondItemDiscountPercentCache;
    }

    /**
     * Остаток товара в выбранном городе (для сортировки: в наличии сначала).
     */
    protected function productCityQuantity(Product $product, ?int $cityId): int
    {
        if ($cityId === null) {
            return 0;
        }
        if ($product->relationLoaded('cityCounts')) {
            $count = $product->cityCounts->where('city_id', $cityId)->first();

            return (int) ($count->quantity ?? 0);
        }

        return (int) ($product->city_quantity ?? 0);
    }

    public function index(Request $request, ?string $category = null) // <--- Параметр соответствует маршруту
    {
        $data = $request->validate([
            // 'category' => ['nullable', 'string'],
            'sort' => ['nullable', 'string', Rule::in(['pop', 'rating', 'cheaper', 'expensive', 'new', ])],
            'fast_tag' => ['nullable', 'array'],
            'fast_tag.*' => ['string', 'exists:tags,slug'],
            'price_from' => ['nullable', 'numeric'],
            'price_to' => ['nullable', 'numeric'],
            'weight_from' => ['nullable', 'numeric'],
            'weight_to' => ['nullable', 'numeric'],
            'manufacturers' => ['nullable', 'array'],
            'manufacturers.*' => ['integer', 'exists:manufacturers,id'],
            'box_type' => ['nullable', 'array'],
            'box_type.*' => ['string'],
            'cashback_percent' => ['nullable', 'array'],
            'cashback_percent.*' => ['numeric'],
        ]);

        $hasValue = fn($value) => !is_null($value) && $value !== '';
        $hasPriceFrom = array_key_exists('price_from', $data) && $hasValue($data['price_from']);
        $hasPriceTo = array_key_exists('price_to', $data) && $hasValue($data['price_to']);
        $hasWeightFrom = array_key_exists('weight_from', $data) && $hasValue($data['weight_from']);
        $hasWeightTo = array_key_exists('weight_to', $data) && $hasValue($data['weight_to']);
        $hasManufacturers = !empty($data['manufacturers']);
        $hasBoxType = !empty($data['box_type']);
        $hasCashback = !empty($data['cashback_percent']);

        if(!isset($data['sort']) && !$request->input('search')) $is_products_group = true; else $is_products_group = false;
        if(
            $hasPriceFrom || $hasPriceTo ||
            $hasWeightFrom || $hasWeightTo ||
            $hasManufacturers || $hasBoxType || $hasCashback
        ) {
            $is_products_group = false; $filterOn = true;
        } else {
            $filterOn = false;
        }

        $cityId = session('user_city')['city_id'] ?? null;

        $categoryTarget = null;
        // --- НАЧАЛО МИНИМАЛЬНОГО ИЗМЕНЕНИЯ ---
        // Определяем $categoryTarget и $categoriesProductGroup через $category из маршрута /category/{category?}
        $categoryCombinations = collect();
        if ($category) {
            $categoryTarget = $this->resolveCategoryByPath($category);

            if (!$categoryTarget) {
                abort(404, 'Категория не найдена: ' . $category);
            }

            $categoryCombinations = $categoryTarget->combinations()->where('is_active', true)->get();

            if($categoryTarget->children()->exists()) { // <--- Используем отношение
                $categoriesProductGroup = $categoryTarget->children; // <--- Получаем детей
            } else {
                $categoriesProductGroup = new Collection([$categoryTarget]); // <--- Коллекция с одной
            }
        } else {
            // Если $category нет, используем старую логику для получения $categoriesProductGroup
            $categoriesProductGroup = Category::getParentLvl()->get();
        }
        // --- КОНЕЦ МИНИМАЛЬНОГО ИЗМЕНЕНИЯ ---

        // --- ВСЯ ОСТАЛЬНАЯ ЛОГИКА ОСТАЕТСЯ БЕЗ ИЗМЕНЕНИЙ ---
        $categorySecondItemPercent = collect();

        if($is_products_group) {
            $productsCollectionByCategories = new Collection;
            foreach ($categoriesProductGroup as $category) {
                $query = $category->products()->with('tags', 'cityCounts')->active();
                if ($request->filled('fast_tag') && $request->fast_tag != null) {
                    $fastTags = $request->fast_tag;
                    $query->WhereHas('tags', function ($q) use ($fastTags) {
                        return $q->whereIn('slug', $fastTags);
                    });
                }
                $products = ProductCardResource::collection($query->get());
                foreach ($products as $product) {
                    $product->category_group_name = $category->name;
                }

                $productsCollectionByCategories = $productsCollectionByCategories->merge($products);

                $categorySecondItemPercent->put(
                    $category->name,
                    $category->has_second_item_discount ? $this->getSecondItemDiscountPercentValue() : null
                );
            }

            // По умолчанию: сначала в наличии (можно в корзину), потом «ожидаются»
            $productsCollectionByCategories = $productsCollectionByCategories->sortByDesc(function ($resource) use ($cityId) {
                return $this->productCityQuantity($resource->resource, $cityId) > 0 ? 1 : 0;
            })->values();

            $productsPrependPaginate = $productsPrependFilter = $productsCollectionByCategories;
            $productsCollectionByCategoriesPaginator = $productsCollectionByCategories->paginate(30);
            $productsCollectionByCategoriesGroup = $productsCollectionByCategoriesPaginator->getCollection()
                ->groupBy('category_group_name')
                ->map(function ($group, $categoryName) use ($categorySecondItemPercent) {
                    $items = $group->values()->all();
                    $items[] = [
                        '__meta__' => true,
                        'second_item_discount_percent' => $categorySecondItemPercent->get($categoryName),
                    ];
                    return $items;
                });
            $products = $productsCollectionByCategoriesPaginator->setCollection(($productsCollectionByCategoriesGroup));

        } else {

            $query = Product::query()->active();

            // фильтр по категории (включая дочерние)
            $query = $query->with('product_categories.category')
                ->whereHas('product_categories.category', function (Builder $q) use ($categoriesProductGroup) {
                    return $q->whereIn('categories.id', $categoriesProductGroup->pluck('id')->toArray());
                });

            // быстрые фильтры | теги
            if ($request->filled('fast_tag') && $request->fast_tag != null) {
                $fastTags = $request->fast_tag;
                $query->WhereHas('tags', function ($q) use ($fastTags) {
                    return $q->whereIn('slug', $fastTags);
                });
            }

            // поисковой запрос:
            if ($request->filled('search')) {
                $query = $this->searchQuery($request->input('search'), $query);
            }

            $products = $query->with('tags', 'brands', 'manufacturer', 'attributes', 'Globalpromotion', 'cityCounts')->get();

            $productsPrependFilter = $products;

            // фильтры
            if($filterOn) {
                $products = $this->filterCollection($data, $products);
            }

            // сортировка: при выборе пользователя — по выбранному полю, иначе — сначала в наличии, потом «ожидаются»
            if (isset($data['sort'])) {
                $products = $this->sortCollection($data['sort'], $products);
            } else {
                $products = $products->sortByDesc(fn (Product $p) => $this->productCityQuantity($p, $cityId) > 0 ? 1 : 0)->values();
            }

            $productsPrependPaginate = $products;

            $products = $products->paginate(30);

            $products = ProductCardResource::collection($products);

            //строка-фикс, чтобы на фронте не было дублирования вложенности data и пагинации
            $products = $products->setCollection(New Collection($products->getCollection()));

        }

        // заполняем быстрые теги и варианты значений в фильтрах
        $fast_filter_tags = new Collection;
        $manufacturers_filter = new Collection;
        $box_type_filter = new Collection;
        $cashback_percent_filter = new Collection;
        foreach ($productsPrependFilter as $product) {
            foreach ($product->tags as $tag) {
                if(!$fast_filter_tags->contains('id', $tag->id)) $fast_filter_tags->push($tag);
            }
            if ($product->manufacturer && !$manufacturers_filter->contains('id', $product->manufacturer->id)) {
                $manufacturers_filter->push($product->manufacturer);
            }
            foreach ($product->attributes->where('slug', 'tip-upakovki') as $boxTypeAttribute) {
                if(!$box_type_filter->contains(null, $boxTypeAttribute->pivot->value)) $box_type_filter->push($boxTypeAttribute->pivot->value);
            }
            // Собираем проценты кэшбека из активных глобальных акций типа ProductCashback
            $cashbackPercent = $product->getCashbackPercent();
            if ($cashbackPercent !== null) {
                $exists = $cashback_percent_filter->contains(function ($value) use ($cashbackPercent) {
                    return abs($value - $cashbackPercent) < 0.01; // Сравнение float с небольшой погрешностью
                });
                if (!$exists) {
                    $cashback_percent_filter->push($cashbackPercent);
                }
            }
        }


        $filterSource = $productsPrependFilter;

        $minPrice = $filterSource
            ->map(fn($p) => min(
                array_filter([$p->sale_price, $p->totalPrice], fn($v) => $v !== null)
            ))
            ->min();
        $filterData = [
            'price_min' => $minPrice,
            'price_max' => $filterSource->max('totalPrice'),
            'weight_min' => $filterSource->min('totalWeightKilo'),
            'weight_max' => $filterSource->max('totalWeightKilo'),
            'manufacturers' => ManufacturerShortResource::collection($manufacturers_filter),
            'box_type' => $box_type_filter,
            'cashback_percent' => $cashback_percent_filter->sort()->values(),
        ];

        // логика рекомендованных
        $recommendedProductsIds = RecomendedProductPage::where('slug', PageSlugEnum::Category->value)
            ->first()
            ?->recomendedProducts
            ->pluck('product_id')
            ->toArray() ?? [];

        $recommendedProducts = Product::active()->whereIn('id', $recommendedProductsIds)
            ->orderBy('id')
            ->get();


        //сео данные
        $seoData = null;

        if ($categoryTarget) {
            if ($categoryTarget->seo_title || $categoryTarget->seo_description || $categoryTarget->keywords) {
                $seoData = [
                    'page_name' => $categoryTarget->name,
                    'seo_title' => $categoryTarget->seo_title,
                    'seo_description' => $categoryTarget->seo_description,
                    'seo_keywords' => $categoryTarget->keywords,
                ];
            }
        }

        if (!$seoData) {
            $seoData = SeoPage::where('slug', 'catalog')->first();
        }
        return Inertia::render('catalog/ui/CatalogPage', [
            'seoData' => $seoData ? new SeoPageResource($seoData) : null,
            'category_target' => $categoryTarget ? CategoryItemTreeResource::make($categoryTarget) : null, // <--- Передаем найденную категорию
            'categories' => CategoryResource::collection(Category::getParentLvl()->get()),
            'products' => $products,
            'products_recommended' => ProductCardResource::collection($recommendedProducts),
            'is_products_group' => $is_products_group,
            'fast_filter_tags' => TagResource::collection($fast_filter_tags),
            'reviews' => ReviewHomeCardResource::collection(Review::where('status',ReviewStatusEnum::Approved)->where('show_catalog',true)->get()),
            'filter_data' => $filterData,
            'combinations' =>  CombinationCardResource::collection($categoryCombinations),
        ]);
    }
    public function filterCollection (array $data, Collection $collection) : Collection {
        $hasValue = fn($value) => !is_null($value) && $value !== '';

        if(array_key_exists('price_from', $data) && $hasValue($data['price_from'])) {
            $collection = $collection->where('totalPrice', '>=', $data['price_from']);
        }
        if(array_key_exists('price_to', $data) && $hasValue($data['price_to'])) {
            $collection = $collection->where('totalPrice', '<=', $data['price_to']);
        }
        if(array_key_exists('weight_from', $data) && $hasValue($data['weight_from'])) {
            $collection = $collection->where('totalWeightKilo', '>=', $data['weight_from']);
        }
        if(array_key_exists('weight_to', $data) && $hasValue($data['weight_to'])) {
            $collection = $collection->where('totalWeightKilo', '<=', $data['weight_to']);
        }

        if(!empty($data['manufacturers'])) {
            $manufacturerIds = array_map('intval', $data['manufacturers']);
            $collection = $collection->filter(function ($item) use ($manufacturerIds) {
                return $item->manufacturer_id !== null && in_array((int) $item->manufacturer_id, $manufacturerIds, true);
            });
        }

        if(!empty($data['box_type'])) {
            $box_types = $data['box_type'];
            $collection = $collection->filter(function ($item) use ($box_types) {
                return $item->attributes_values->where('attribute.slug', 'tip-upakovki')->whereIn('value', $box_types)->first();
            });
        }

        if(!empty($data['cashback_percent'])) {
            $cashbackPercents = array_map('floatval', $data['cashback_percent']);
            $collection = $collection->filter(function ($item) use ($cashbackPercents) {
                $productCashbackPercent = $item->getCashbackPercent();
                if ($productCashbackPercent === null) {
                    return false;
                }
                foreach ($cashbackPercents as $filterPercent) {
                    if (abs($productCashbackPercent - $filterPercent) < 0.01) {
                        return true;
                    }
                }
                return false;
            });
        }

        return $collection;
    }

    public function searchQuery(string $search, Builder $query): Builder
    {
        $search = mb_strtolower($search);

        // Проверяем, есть ли продукт с артикулом, который полностью совпадает с запросом
        // Выполняем подзапрос или отдельный запрос для проверки точного совпадения
        $exactArticleMatches = $query->getModel() // Получаем модель (например, Product)
        ->newQuery() // Создаем новый экземпляр запроса для модели
        ->select('id') // Выбираем только ID для эффективности
        ->whereRaw('LOWER(article_number) = ?', [$search])
            ->get();

        if ($exactArticleMatches->isNotEmpty()) {
            // Если есть точное совпадение по артикулу, ограничиваем основной запрос этими ID
            $productIds = $exactArticleMatches->pluck('id')->toArray();
            $query->whereIn('id', $productIds);
            // Возвращаем измененный запрос, не применяя другие условия
            return $query;
        } else {
            // Если нет точного совпадения по артикулу, применяем стандартный поиск по другим полям
            // Исключаем частичный поиск по article_number
            $query->where(function ($qBuilder) use ($search) {
                $qBuilder
                    ->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    // ->orWhereRaw('LOWER(article_number) LIKE ?', ["%{$search}%"]) // Исключаем частичный поиск
//                    ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"])
//                    ->orWhereRaw('LOWER(short_description) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(keywords) LIKE ?', ["%{$search}%"])
                    ->orWhereHas('categories', fn($cat) =>
                    $cat->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    )
                    ->orWhereHas('brands', fn($brand) =>
                    $brand->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    )
                    ->orWhereHas('attributes_values', fn($attr) =>
                    $attr->whereRaw('LOWER(value) LIKE ?', ["%{$search}%"])
                    );
            });
        }

        return $query;
    }
    protected function resolveCategoryByPath(string $path): ?Category
    {
        $slugs = array_values(array_filter(explode('/', $path), fn($slug) => $slug !== ''));
        if (empty($slugs)) {
            return null;
        }

        $current = Category::query()
            ->whereNull('parent_category_id')
            ->where('slug', $slugs[0])
            ->first();

        if (!$current) {
            return null;
        }

        for ($i = 1; $i < count($slugs); $i++) {
            $current = $current->children()->where('slug', $slugs[$i])->first();
            if (!$current) {
                return null;
            }
        }

        return $current;
    }
    public function sortCollection(string $sort, Collection $collection) : Collection {
        if($sort == 'pop') {
            $collection = $collection->sortByDesc('created_at');
        }
        if($sort == 'new') {
            $collection = $collection->sortByDesc('created_at');
        }
        if($sort == 'rating') {
            $collection = $collection->sortByDesc(function ($product, int $key) {
                return $product->rating;
            });
        }
        if($sort == 'cheaper') {
            $collection = $collection->sortBy(function ($product, int $key) {
                return $product->totalPrice;
            });
        }
        if($sort == 'expensive') {
            $collection = $collection->sortByDesc(function ($product, int $key) {
                return $product->totalPrice;
            });
        }
        return $collection->values();
    }

}

//TODO не удалять
/*$categoriesId = Category::getParentLvl()->pluck('id')->toArray();
$products = $products->with('product_categories.category')
    ->whereHas('product_categories.category', function (Builder $q) use ($categoriesId) {
        return $q->whereIn('categories.id', $categoriesId);
    })->orderByDesc('product_categories.category_id');
dd($products);*/

/*echo '<pre>';
        var_dump($products->toJson(JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
  echo '</pre>';
  exit();*/

/*  dd($products->toJson(JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));*/
/*  dd(json_encode($products, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));*/

/*        dd(json_encode($filterData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));*/
