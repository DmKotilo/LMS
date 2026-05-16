<?php

namespace GlobalPromotion\Http\Controllers;

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
use Product\Models\Product;
use Promotion\Models\Promotion;
use Recipe\Models\Recipe;
use Recipe\Models\RecipeSelection;
use Review\Models\Review;
use SeoPage\Models\SeoPage;


class GlobalPromotionController extends Controller
{

}
