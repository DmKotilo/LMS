<?php

namespace Combo\Http\Controllers;

use Address\Http\Resources\AddressResource;
use App\Http\Controllers\Controller;
use App\Modules\Banner\src\Http\Enums\BannerTypeEnum;
use App\Modules\Banner\src\Http\Resources\BannerResource;
use App\Modules\Combo\src\Http\Resources\ComboCardResource; // Импортируем ресурс карточки
use App\Modules\Combo\src\Http\Resources\ComboDetailResource;
use App\Modules\Faq\src\Enums\FaqSectionPageSlugEnum;
use App\Modules\Faq\src\Http\Resources\FaqSectionResource;
use App\Modules\Faq\src\Models\FaqSection;
use App\Modules\Product\Product\src\Enums\PageSlugEnum;
use App\Modules\Product\Product\src\Models\RecomendedProductPage;
use App\Modules\Review\src\Enums\ReviewStatusEnum;
use App\Modules\Review\src\Http\Resources\ReviewHomeCardResource;
use App\Modules\SeoPage\src\Http\Resources\SeoPageResource;
use Banner\Models\Banner;
use Catalog\Http\Resources\ProductCardResource;
use Combo\Models\Combo; // Импортируем модель Combo
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection; // Для возврата коллекции ресурсов
use Inertia\Inertia;
use Review\Models\Review;
use SeoPage\Models\SeoPage;

class ComboController extends Controller
{

    public function index(Request $request)
    {
        $combosQuery = Combo::query()->hasProducts()->with('products');
        $combos = $combosQuery->get(); // Убираем пагинацию

        $reviews = Review::where('status',ReviewStatusEnum::Approved)->where('show_combo_page',true)->get();

        $recommendedProductsIds = RecomendedProductPage::where('slug', PageSlugEnum::Combo->value)
            ->first()
            ?->recomendedProducts
            ->pluck('product_id')
            ->toArray() ?? [];

        $recommendedProducts = \Product\Models\Product::active()
            ->whereIn('id', $recommendedProductsIds)
            ->orderBy('id')
            ->get();

        $faqSection = FaqSection::where('page_slug', FaqSectionPageSlugEnum::Combo)
            ->with(['faqs' => function ($query) {
                $query->orderBy('position');
            }])->first();

        //сео данные
        $seoData = SeoPage::where('slug','combos')->first();
        return Inertia::render('combo/combo-page/ui/ComboPage', [
            'seoData' => $seoData ? new SeoPageResource($seoData) : null,
            'combos' => ComboCardResource::collection($combos),
            'recommendedProducts' => ProductCardResource::collection($recommendedProducts),
            'reviews' => $reviews ? ReviewHomeCardResource::collection($reviews) : [],
            "faqs" => $faqSection ? new FaqSectionResource($faqSection) : null,
        ]);
    }

    public function show(Request $request, Combo $combo)
    {
        // Проверяем, что у комбонабора есть хотя бы один товар
        if ($combo->products()->count() === 0) {
            abort(404);
        }

        $combo->loadMissing([
            'products' => function ($query) {
                $query->where('is_active', true)->with('images');
            },
            'promotion',
            'images',
            'firstImage',
            'reviews.user',
        ]);

//        $banners = Banner::where('is_active', true)
//            ->where('banner_type', BannerTypeEnum::ComboDetail)
//            ->get();

        $banners = Banner::where('is_active', true)
            ->where('banner_type', BannerTypeEnum::ComboDetail)
            ->orderBy('created_at', 'ASC')
            ->get();

        return Inertia::render('combo/combo-detail/ui/ComboDetailPage', [
            'combo' => new ComboDetailResource($combo),
            'banners' => BannerResource::collection($banners),
            'userAddresses' => auth()->user() ? AddressResource::collection(auth()->user()->addresses) : null,
        ]);

    }
}
