<?php

namespace Promotion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Banner\src\Http\Enums\BannerTypeEnum;
use App\Modules\Banner\src\Http\Resources\BannerResource;
use App\Modules\Blog\Recipe\src\Http\Resources\RecipeCardResource;
use App\Modules\Blog\Recipe\src\Http\Resources\RecipeCategoriesResource;
use App\Modules\Faq\src\Enums\FaqSectionPageSlugEnum;
use App\Modules\Faq\src\Http\Resources\FaqSectionResource;
use App\Modules\Faq\src\Models\FaqSection;
use App\Modules\Product\Product\src\Enums\PageSlugEnum;
use App\Modules\Product\Product\src\Models\RecomendedProductPage;
use App\Modules\Promotion\src\Http\Resources\PromotionCardResource;
use App\Modules\Promotion\src\Http\Resources\PromotionDetailResource;
use App\Modules\Promotion\src\Http\Resources\PromotionTypeResource;
use App\Modules\Review\src\Enums\ReviewStatusEnum;
use App\Modules\Review\src\Http\Resources\ReviewHomeCardResource;
use App\Modules\SeoPage\src\Http\Resources\SeoPageResource;
use Banner\Models\Banner;
use Catalog\Http\Resources\ProductCardResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Combo\Models\Combo;
use Product\Models\Product;
use Promotion\Models\Promotion;
use Recipe\Models\RecipeSelection;
use Review\Models\Review;
use SeoPage\Models\SeoPage;

#[Group('Промоакции')]
class PromotionController extends Controller
{

    #[Endpoint('Список промоакций', 'Получение списка опубликованных промоакций с возможностью  фильтра по типу')]
    #[QueryParam('type', 'Массив типов акций (enum: discount, gift, seasonal) для фильтрации', required: false, example: '["discount","seasonal"]')]
    #[ResponseFromApiResource(PromotionCardResource::class, Promotion::class,description: 'Список промоакций', collection: true)]
    #[ResponseFromApiResource(PromotionTypeResource::class,description: 'Список типов акций')]
    public function index(Request $request){

        $query = Promotion::where('is_active', true);

        if ($types = $request->input('type')) {
            $query->whereIn('type', (array)$types);
        }

        $userCity = session('user_city');

        if (!empty($userCity['city_id'])) {
            $query->whereHas('cities', function ($q) use ($userCity) {
                $q->where('cities.id', $userCity['city_id']);
            });
        }

        $promotions = $query->orderByDesc('created_at')->get();
        $reviews = Review::where('status',ReviewStatusEnum::Approved)->where('show_promotion_page',true)->get();
//        $banners = Banner::where('is_active',true)->where('banner_type',BannerTypeEnum::PromotionPage)->orderBy('order', 'ASC')->get();

        $banners = Banner::where('is_active', true)
            ->where('banner_type', BannerTypeEnum::PromotionPage)
            ->orderBy('created_at', 'ASC')
            ->get();

        // рекомендуемые товары
        $recommendedProductsIds = RecomendedProductPage::where('slug', PageSlugEnum::Promotion->value)
            ->first()?->recomendedProducts()
            // Применяем whereHas к связи 'product' внутри RecomendedProduct
            ->whereHas('product', fn($q) => $q->active())
            ->pluck('product_id') // <-- Правильное поле для извлечения
            ->toArray() ?? [];

        $recommendedProducts = Product::active()
            ->whereIn('id', $recommendedProductsIds)
            ->orderBy('id')
            ->get();


//        $section = FaqSection::where('name','=','Страница промоакций')->with(['faqs' => function ($query) {
//            $query->orderBy('position');
//        }])->orderBy('id')->first();
        $section = FaqSection::where('page_slug', FaqSectionPageSlugEnum::Promo)
            ->with(['faqs' => function ($query) {
                $query->orderBy('position');
            }])->first();

        //сео данные
        $seoData = SeoPage::where('slug','promotions')->first();
        return Inertia::render('promo/promo-page/ui/PromoPage',[
            'seoData' => $seoData ? new SeoPageResource($seoData) : null,            'promotionTypes' => new PromotionTypeResource(null),
            'promotions' => PromotionCardResource::collection($promotions),
            'recommendedProducts' => ProductCardResource::collection($recommendedProducts),
            'reviews' => $reviews ? ReviewHomeCardResource::collection($reviews) : [],
            "faqs" =>$section ? new FaqSectionResource($section) : null,
            'banners' => $banners ? BannerResource::collection($banners) : [],
        ]);

    }

    public function show(Request $request, Promotion $promotion){
        $promotion->load([
            'products' => fn($q) => $q->active(),
            'combos' => fn($q) => $q->hasProducts(),
            'image',
        ]);
        if ($promotion->products->count() == 1 && $promotion->combos->count() == 0) {
            $product = $promotion->products->first();
            return redirect()->route('catalog.product.show', ['product' => $product->slug]);
        }

        if ($promotion->combos->count() == 1 && $promotion->products->count() == 0) {
            $combo = $promotion->combos->first();
            return redirect()->route('combo.show', ['combo' => $combo->id]);
        }

        $recommendedProductsIds = RecomendedProductPage::where('slug', PageSlugEnum::Promotion->value)
            ->first()?->recomendedProducts
            ->pluck('product_id')
            ->toArray() ?? [];

        $count = count($recommendedProductsIds);

        if ($count === 0) {
            //если товаров нет, то берем случайные
            $recommendedProducts = Product::active()
                ->inRandomOrder()
                ->limit(10)
                ->get();
        } elseif ($count < 7) {
            // если меньше 7(не хватает для слайдера), то добавляем
            $missing = 10 - $count;

            $extra = Product::active()
                ->whereNotIn('id', $recommendedProductsIds)
                ->inRandomOrder()
                ->limit($missing)
                ->get();

            $recommendedProducts = Product::active()
                ->whereIn('id', $recommendedProductsIds)
                ->orderBy('id')
                ->get()
                ->merge($extra);
        } else {
            $recommendedProducts = Product::active()
                ->whereIn('id', $recommendedProductsIds)
                ->orderBy('id')
                ->get();
        }
        $reviews = Review::where('status',ReviewStatusEnum::Approved)->where('show_promotion_page',true)->get();


//        $section = FaqSection::where('name','=','Страница промоакций')->with(['faqs' => function ($query) {
//            $query->orderBy('position');
//        }])->orderBy('id')->first();
        $section = FaqSection::where('page_slug', FaqSectionPageSlugEnum::Promo)
            ->with(['faqs' => function ($query) {
                $query->orderBy('position');
            }])->first();

        return Inertia::render('promo/promo-detail-page/ui/PromoDetailPage',[
            'promotion' => new PromotionDetailResource($promotion),
            'recommendedProducts' => ProductCardResource::collection($recommendedProducts),
            'reviews' => $reviews ? ReviewHomeCardResource::collection($reviews) : [],
            "faqs" =>$section ? new FaqSectionResource($section) : null,
        ]);
    }
}
