<?php

namespace Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use Brand\Models\Brand;
use Catalog\Http\Resources\ProductCardResource;
use Catalog\Http\Resources\ProductSearchCardResource;
use Category\Models\Category;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Product\Models\Product;
use Illuminate\Http\Request;

#[Group('Каталог')]
class ProductSearchController extends Controller
{
    #[Endpoint(
        title: 'Автодополнение по товарам',
        description: 'Возвращает список товаров, подходящих под поисковый запрос. Ищет в названии, описании, ключевых словах, брендах, категориях и значениях атрибутов.'
    )]
    #[QueryParam(name: 'search', description: 'Поисковый запрос (например, "prime" или "пшеничный")', required: true, example: 'prime')]
    #[ResponseFromApiResource(ProductSearchCardResource::class,Product::class)]
    public function autocomplete(Request $request)
    {
        $query = mb_strtolower($request->input('search'));

        // Проверяем, есть ли продукт с артикулом, который полностью совпадает с запросом
        $exactArticleMatches = Product::select('id') // Выбираем только ID для эффективности
        ->active()
        ->whereRaw('LOWER(article_number) = ?', [$query])
            ->get();

        $productsQuery = Product::query()->with(['brands', 'categories', 'attributes_values'])->active();

        if ($exactArticleMatches->isNotEmpty()) {
            // Если есть точное совпадение по артикулу, ищем только по этим ID и добавляем другие условия

            $productIds = $exactArticleMatches->pluck('id')->toArray();
            $productsQuery->whereIn('id', $productIds);
        } else {
            // Если нет точного совпадения по артикулу, исключаем его из поиска и ищем по другим полям
            $productsQuery->where(function ($q) use ($query) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$query}%"])
                    // ->orWhereRaw('LOWER(article_number) LIKE ?', ["%{$query}%"]) // Убираем эту строку
//                    ->orWhereRaw('LOWER(description) LIKE ?', ["%{$query}%"])
//                    ->orWhereRaw('LOWER(short_description) LIKE ?', ["%{$query}%"])
                    ->orWhereRaw('LOWER(keywords) LIKE ?', ["%{$query}%"])
                    ->orWhereHas('categories', fn($cat) =>
                    $cat->whereRaw('LOWER(name) LIKE ?', ["%{$query}%"])
                    )
                    ->orWhereHas('brands', fn($brand) =>
                    $brand->whereRaw('LOWER(name) LIKE ?', ["%{$query}%"])
                    )
                    ->orWhereHas('attributes_values', fn($attr) =>
                    $attr->whereRaw('LOWER(value) LIKE ?', ["%{$query}%"])
                    );
            });
        }

        $products = $productsQuery->limit(10)->get();

        return ProductSearchCardResource::collection($products);
    }


}
