<?php

namespace App\Http\Middleware;

use App\Http\Middleware\CaptureUtmParameters;
use App\Modules\Bonus\Bonus\src\Enums\BonusStatusEnum;
use App\Modules\Bonus\Bonus\src\Enums\BonusTypeEnum;
use App\Modules\Bonus\Bonus\src\Http\Resources\BonusCardResource;
use App\Modules\Cart\src\Enums\CartStatusEnum;
use App\Modules\Cart\src\Http\Resources\CartResource;
use App\Modules\Cart\src\Traits\GetCart;
use App\Modules\City\src\Http\Resources\CityResource;
use App\Modules\Localization\src\Http\Resources\LangResource;
use App\Services\BreadcrumbService;
use Authorization\Models\SocialAuthSetting;
use Bonus\Services\BonusService;
use Carbon\Carbon;
use Cart\Models\UserCart;
use Catalog\Http\Resources\ProductCardResource;
use Category\Http\Resources\CategoryResource;
use Category\Models\Category;
use City\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Middleware;
use Localization\Models\Lang;
use Product\Models\Product;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    use GetCart;
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Set the root template that's loaded on the first Inertia page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     */
    public function rootView(Request $request): string
    {
        return 'app';
    }

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
//        $cart = $this->getCart($request)->load('items.item');
//        $cart = $this->getCart($request)->load(['items.item' => fn($q) => $q->active()]);
        $cart = $this->getCart($request)->load(['items.item' => function ($q) {
            $q->when($q->getModel() instanceof Product, fn($query) => $query->active());
        }]);


        $userBonusCard = null;
        $bonusCount = 0;
        $oldestBonusCount = 0;
        $expireDate = null;
        $daysLeft = null;

        if (auth()->check()) {
            $user = auth()->user();
            $userBonusCard = $user->card;
            $currentLevel = $user->loyaltyLevel?->loyaltyLevel;

            // Используем новый сервис для получения баланса
            $bonusService = app(BonusService::class);
            $bonusCount = $bonusService->getAvailableBalance($user);

            // Получаем ближайшую дату истечения и суммируем ВСЕ бонусы с этой датой
            // НЕ путать с FIFO при списании (там списываем по active_date)
            $now = Carbon::now();
            $closestExpiryDate = $user->bonusHistories()
                ->where('type', BonusTypeEnum::Accrual)
                ->where('status', BonusStatusEnum::Active)
                ->where('remaining_amount', '>', 0)
                ->where('active_date', '<=', $now)
                ->whereNotNull('expires_at')
                ->where('expires_at', '>', $now)
                ->orderBy('expires_at', 'asc')
                ->value('expires_at');

            if ($closestExpiryDate) {
                // Суммируем ВСЕ бонусы, которые истекают в эту дату
                $oldestBonusCount = round($user->bonusHistories()
                    ->where('type', BonusTypeEnum::Accrual)
                    ->where('status', BonusStatusEnum::Active)
                    ->where('remaining_amount', '>', 0)
                    ->where('expires_at', $closestExpiryDate)
                    ->sum('remaining_amount'));

                $expireDate = Carbon::parse($closestExpiryDate);
                $daysLeft = max(0, $now->diffInDays($expireDate, false));
            }
        } else {
            $currentLevel = null;
        }

//        $cart->load('items.item');
//        $cart = $this->getCart($request)->load(['items.item' => fn($q) => $q->active()]);
        $cart = $this->getCart($request)->load(['items.item' => function ($q) {
            $q->when($q->getModel() instanceof Product, fn($query) => $query->active());
        }]);

        // true только когда ВСЕ (неподарочные) товары участвуют в промоакции — тогда промокод применить нельзя
        $hasPromoItems = false;
        $payableItems = $cart->items->filter(fn ($cartItem) => !$cartItem->is_gift && $cartItem->item);
        if ($payableItems->isNotEmpty()) {
            $hasPromoItems = $payableItems->every(function ($cartItem) {
                $item = $cartItem->item;
                return method_exists($item, 'promotion') && $item->promotion()->exists();
            });
        }

        $canLeaveReview = true;
        $reviewsLeft = 3;

        if (auth()->check()) {
            $reviewsCount = \Review\Models\Review::where('user_id', auth()->id())
                ->where('created_at', '>=', \Carbon\Carbon::now()->startOfMonth())
                ->count();
            $canLeaveReview = $reviewsCount < 3;
            $reviewsLeft = max(0, 3 - $reviewsCount);
        }


        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'translations' => function () {
                $dir = base_path('resources/js/src/shared/i18n/messages');
                $translations = [];

                if (File::isDirectory($dir)) {
                    foreach (File::files($dir) as $file) {
                        if ($file->getExtension() !== 'json') {
                            continue;
                        }

                        $translations[$file->getFilenameWithoutExtension()] = json_decode(
                            File::get($file->getPathname()),
                            true
                        );
                    }
                }

                return $translations;
            },
            'userBonusCard' => isset($userBonusCard)
                ? new BonusCardResource($userBonusCard)
                : null,
            'user_city' => function () {
                // если в сессии уже есть город, берём его
                if (session()->has('user_city')) {
                    return session('user_city');
                }

                $moscow = City::where('name', 'Москва')->first();
                if ($moscow) {
                    $data = [
                        'city_id' => $moscow->id,
                        'city' => $moscow->name,
                        'latitude' => $moscow->latitude,
                        'longitude' => $moscow->longitude,
                    ];
                    session()->put('user_city', $data);
                    return $data;
                }

                return null;
            },
            'cities' => CityResource::collection(
                City::query()->active()->ordered()->get()
            ),
            'user_lang' => function () {
                return session('user_lang', null);
            },
            'languages' => LangResource::collection(Lang::orderBy('id', 'asc')->get()),
            'categories' => CategoryResource::collection(Category::getTree()),
            'cart' => new CartResource($cart),
            'cart_has_promo_items' => $hasPromoItems,
            'flash' => [
                'success' => $request->session()->get('success'),
                'message' => $request->session()->get('message'),
                'error' => $request->session()->get('error'),
                'errors' => $request->session()->get('errors') ? $request->session()->get('errors')->all() : null,
                'applied_promocode' => $request->session()->get('applied_promocode'),
            ],
            'ecommerce_order_data' => $request->session()->pull('ecommerce_order_data'),
            'currentLevel' => $currentLevel?->name,
            'allBonusCount' => (int) round($bonusCount),
            'oldestBonusCount' => (int) round($oldestBonusCount),
            'expireDate' => $expireDate ? $expireDate->format('d.m.Y') : null,
            'daysLeft' => (int) $daysLeft,

            'canLeaveReview' => $canLeaveReview,
            'reviewsLeft' => $reviewsLeft,
            'noveltyProducts' => ProductCardResource::collection(
                Product::active()->where('is_new', true)->get()
            ),

            'social_auth' => [
                'vk_available' => SocialAuthSetting::where('name', 'vk')->value('is_available'),
                'yandex_available' => SocialAuthSetting::where('name', 'yandex')->value('is_available'),
            ],
            'breadcrumbs' => function () use ($request) {
                $breadcrumbService = app(BreadcrumbService::class);

                // Проверяем, есть ли кастомные breadcrumbs
                $customBreadcrumbs = $request->attributes->get('custom_breadcrumbs');

                if ($customBreadcrumbs) {
                    return $customBreadcrumbs;
                }

                return $breadcrumbService->generate($request);
            },
            'ziggy' => function () use ($request) {
                return (new Ziggy)->toArray();
            },
            // UTM только в URL: из сессии (для Bitrix) отдаём во фронт, чтобы подставлять в ссылки — одно место хранения.
            'utm_for_links' => $request->session()->get(CaptureUtmParameters::SESSION_KEY, []),
        ];
    }

}
