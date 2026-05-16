<?php

namespace Home\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Modules\Banner\src\Http\Enums\BannerTypeEnum;
use App\Modules\Banner\src\Http\Resources\BannerResource;
use App\Modules\Blog\Recipe\src\Http\Resources\RecipeCardResource;
use App\Modules\Bonus\Bonus\src\Http\Resources\BonusCardResource;
use App\Modules\Combo\src\Http\Resources\ComboCardResource;
use App\Modules\Order\src\Http\Resources\OrderItemResource;
use App\Modules\Product\Product\src\Enums\PageSlugEnum;
use App\Modules\Product\Product\src\Models\RecomendedProductPage;
use App\Modules\Review\src\Enums\ReviewStatusEnum;
use App\Modules\Review\src\Http\Resources\ReviewHomeCardResource;

use App\Modules\SeoPage\src\Http\Resources\SeoPageResource;
use Banner\Models\Banner;
use Catalog\Http\Resources\ProductCardResource;
use Category\Http\Resources\MainCategoryResource;
use Category\Models\MainCategory;
use Combo\Models\Combo;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Localization\Models\Localization;
use Product\Models\Product;
use Recipe\Models\Recipe;
use Review\Models\ProductReview;
use Review\Models\Review;
use SeoPage\Models\SeoPage;


class HomeController extends Controller
{
    public function index(Request $request)
    {
//        $MainBanners = Banner::where('is_active',true)->where('banner_type',BannerTypeEnum::MainSlider)->orderBy('order', 'ASC')->get();
        $ComboPacksBanner = Banner::where('is_active',true)->where('banner_type',BannerTypeEnum::ComboPacks)->first();
//        $InfoBanners = Banner::where('is_active',true)->where('banner_type',BannerTypeEnum::InfoBlock)->orderBy('order', 'ASC')->get();
        $NoveltyBanner = Banner::where('is_active',true)->where('banner_type',BannerTypeEnum::Novelty)->first();
        $recipes = Recipe::where('publish',true)->where('show_main',true)->get();
        $reviews = Review::where('status',ReviewStatusEnum::Approved)->where('show_main',true)->get();
        $photoCategories = MainCategory::where('is_active',true)->orderBy('position','asc')->get();

        $MainBanners = Banner::where('is_active', true)
            ->where('banner_type', BannerTypeEnum::MainSlider)
            ->orderByRaw('"order" IS NULL ASC, "order" ASC, "created_at" ASC')
            ->get();

        $InfoBanners = Banner::where('is_active', true)
            ->where('banner_type', BannerTypeEnum::InfoBlock)
            ->orderByRaw('"order" IS NULL ASC, "order" ASC, "created_at" ASC')
            ->get();


        $specialProducts = Product::active()
            ->whereHas('promotion', function ($q) {
                $q->where('promotions.is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('promotions.end_date')
                            ->orWhere('promotions.end_date', '>=', now());
                    });
            })
            ->distinct()
            ->orderBy('id')
            ->limit(10)
            ->get();
        $recommendedProductsIds = RecomendedProductPage::where('slug', PageSlugEnum::Main->value)
            ->first()
            ?->recomendedProducts
            ->pluck('product_id')
            ->toArray() ?? [];

        $recommendedProducts = Product::active()->whereIn('id', $recommendedProductsIds)
            ->orderBy('id')
            ->get();

//        $noveltyProducts = Product::whereIn('id', [1, 3, 5, 7, 9, 11, 13])->orderBy('id')->get();
//        $noveltyProducts = Product::active()->where('is_new', true)->get();


//        $comboPacks = Combo::all();
        $comboPacks = Combo::where('show_main', true)->hasProducts()->with('products')->limit(5)->get();


        $userBonusCard = null;
        $orderedItems = collect();

        if (auth()->check()) {
            $orderedItems = auth()->user()
                ->orders()
                ->with('items.item')
                ->get()
                ->pluck('items')
                ->flatten()
                ->filter()
                ->filter(fn($oi) => $oi->item !== null)
                ->unique(fn($oi) => get_class($oi->item) . ':' . $oi->item->id)
                ->values();
        }


        //сео данные
        $seoData = SeoPage::where('slug','home')->first();
        return Inertia::render('main-page/ui/MainPage', [
            'seoData' => $seoData ? new SeoPageResource($seoData) : null,
            'canLogin' => Route::has('login'),
            'orderedProducts' => OrderItemResource::collection($orderedItems), // теперь ок
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
            'mainBanners' => $MainBanners ? BannerResource::collection($MainBanners) : [],
            'comboPacksBanners' => $ComboPacksBanner ?  new BannerResource($ComboPacksBanner) : [],
            'infoBanners' => $InfoBanners ? BannerResource::collection($InfoBanners) : [],
            'noveltyBanner' => $NoveltyBanner ? new BannerResource($NoveltyBanner) : [],
            'photoCategories' => MainCategoryResource::collection($photoCategories),
            'specialProducts' => ProductCardResource::collection($specialProducts),
            'recommendedProducts' => ProductCardResource::collection($recommendedProducts),
//            'noveltyProducts' => ProductCardResource::collection($noveltyProducts),
//            'comboPacks' => ComboCardResource::collection($comboPacks),
//            'noveltyProducts' => ProductCardResource::collection($noveltyProducts),
            'comboPacks' => ComboCardResource::collection($comboPacks),

            'recipes' => $recipes ?  RecipeCardResource::collection($recipes) : [],
            'reviews' => $reviews ? ReviewHomeCardResource::collection($reviews) : [],
        ]);

    }


    public function translationsCheck()
    {
        $dbTranslations = Localization::query()
            ->orderBy('lang_id')
            ->orderBy('field')
            ->get()
            ->groupBy(fn ($item) => $item->lang_id);

        $filesDir = base_path('resources/js/src/shared/i18n/messages');
        $fileTranslations = [];

        if (File::isDirectory($filesDir)) {
            foreach (File::files($filesDir) as $file) {
                if ($file->getExtension() !== 'json') {
                    continue;
                }

                $fileTranslations[$file->getFilename()] = json_decode(
                    File::get($file->getPathname()),
                    true
                );
            }
        }

        return response()->json([
            'db_translations' => $dbTranslations,
            'file_translations' => $fileTranslations,
        ]);
    }
}
