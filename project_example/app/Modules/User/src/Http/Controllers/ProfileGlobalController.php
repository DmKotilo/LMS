<?php

namespace User\Http\Controllers;

use Address\Http\Resources\AddressResource;
use Address\Model\Address;
use Address\Model\ContactMethod;
use Alert\Models\Alert;
use App\Http\Controllers\Controller;
use App\Modules\Alert\src\Http\Resources\AlertResource;
use App\Modules\Banner\src\Http\Enums\BannerLocationEnum;
use App\Modules\Banner\src\Http\Enums\BannerTypeEnum;
use App\Modules\Banner\src\Http\Resources\BannerResource;
use App\Modules\Blog\Recipe\src\Http\Resources\RecipeCardResource;
use App\Modules\Bonus\Bonus\src\Enums\BonusStatusEnum;
use App\Modules\Bonus\Bonus\src\Enums\BonusTypeEnum;
use App\Modules\Bonus\Bonus\src\Http\Resources\BonusCardResource;
use App\Modules\Bonus\Bonus\src\Http\Resources\BonusHistoryResource;
use App\Modules\Bonus\Loyalty\src\Http\Resources\LoyaltyLevelResource;
use App\Modules\Notification\src\Http\Resources\NotificationResource;
use App\Modules\Order\src\Http\Resources\OrderItemResource;
use App\Modules\Order\src\Http\Resources\OrderProfileCardResource;
use App\Modules\Order\src\Http\Resources\OrderProfileDetailResource;
use App\Modules\Order\src\Enums\OrderStatusEnum;
use App\Modules\Order\src\Http\Resources\ProductOrderResource;
use App\Modules\Review\src\Enums\ReviewStatusEnum;
use App\Modules\Review\src\Http\Resources\ReviewResource;
use App\Modules\User\src\Enums\DeleteReasonEnum;
use App\Modules\User\src\Models\DeletedUser;
use App\Modules\UserSetting\src\Http\Resources\UserSettingResource;
use App\MoonShine\Resources\Delivery\DeliveryZoneResource;
use Banner\Models\Banner;
use Carbon\Carbon;
use Catalog\Http\Resources\ProductCardResource;
use Category\Http\Resources\MainCategoryResource;
use Category\Models\MainCategory;
use City\Models\City;
use Delivery\Http\Resources\DeliveryProfileCardResource;
use Delivery\Models\DeliveryZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Loyalty\Models\LoyaltyLevel;
use Notification\Models\PromoNotification;
use Order\Models\Order;
use Recipe\Models\Recipe;
use Review\Models\Review;
use Setting\Models\Setting;
use User\Enums\GenderEnum;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Product\Models\Product;
use User\Models\Subscription;
use User\Models\User;

#[Group("Профиль", "Методы для работы с профилем пользователя")]
class ProfileGlobalController extends Controller
{
    #[Endpoint('Страница Профиля')]
    public function index()
    {
        $user = auth()->user();

        $orderedItems = $user->orders()
            ->where('status', OrderStatusEnum::Completed->value)
            ->with('items.item')
            ->get()
            ->pluck('items')
            ->flatten()
            ->filter()
            ->filter(fn($oi) => $oi->item !== null)
            ->unique(fn($oi) => get_class($oi->item) . ':' . $oi->item->id)
            ->values();

        $favRecipes = $user->whiteList
            ? $user->whiteList->recipes()->with('item')->get()->pluck('item')->filter()->values()
            : collect();

        $allItems = $orderedItems->pluck('item')->merge($favRecipes)
            ->unique(fn($item) => get_class($item) . ':' . $item->id)
            ->values();

        $reviews = Review::whereIn('item_id', $allItems->pluck('id'))
            ->whereIn('item_type', $allItems->map(fn($i) => get_class($i))->unique())
            ->where('user_id', $user->id)
            ->get()
            ->groupBy(fn($r) => $r->item_type . ':' . $r->item_id)
            ->map(fn($group) => $group->sortByDesc('id')->first());

        $isPending = fn($item) =>
            !isset($reviews[get_class($item) . ':' . $item->id]) ||
            $reviews[get_class($item) . ':' . $item->id]->status == ReviewStatusEnum::UserDeleted;

        $pendingCount = $allItems->filter($isPending)->count();


        $notReadedAlertsCount = $user->alerts()->where('is_read', false)->where('user_deleted', false)->count();
        $notReadedNotificationsCount = $user->promoNotifications()->where('is_read', false)->where('user_deleted', false)->where('active_date', '<=', now())->count();
        $notReadedSum = $notReadedAlertsCount + $notReadedNotificationsCount;
//        $userOrders = $user->orders()->get();
//        $userOrders = $user->orders()->orderBy('created_at', 'desc')->get();
        $userOrders = $user->orders()->with('items')->orderBy('created_at', 'desc')->get();

        $profileBanners = Banner::where('is_active', true)
            ->where('location', BannerLocationEnum::ProfilePage)
            ->orderBy('id')
            ->get()
            ->unique('id');

        $totalCount = $profileBanners->count();
        $halfCount = (int) ceil($totalCount / 2);

        $firstBanners = $profileBanners->take($halfCount);
        $secondBanners = $profileBanners->skip($halfCount);

        return Inertia::render('profile/profile-page/ui/ProfilePage', [
            'orderedProducts' => OrderItemResource::collection($orderedItems),
            'deliveries' => DeliveryProfileCardResource::collection($userOrders),
            'itemsWithoutReviewCount' => $pendingCount,
            'notReadedAlertsCount' => (int) $notReadedSum,
            'first_banners' => $firstBanners ?  BannerResource::collection($firstBanners) : null,
            'second_banners' => $secondBanners ?  BannerResource::collection($secondBanners) : null,
        ]);

    }

    #[Endpoint('Страница программы привелегий ')]
    public function privilegeProgram()
    {
        $user = auth()->user();
        $now = Carbon::now();

        // Получаем ближайшую дату истечения и суммируем ВСЕ бонусы с этой датой
        // НЕ путать с FIFO при списании (там списываем по active_date)
        $closestExpiryDate = $user?->bonusHistories()
            ->where('type', BonusTypeEnum::Accrual)
            ->where('status', BonusStatusEnum::Active)
            ->where('remaining_amount', '>', 0)
            ->where('active_date', '<=', $now)
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', $now)
            ->orderBy('expires_at', 'asc')
            ->value('expires_at');

        $daysLeft = null;
        $bonusesExpiringCount = 0;
        $expireDate = null;

        if ($closestExpiryDate) {
            $bonusesExpiringCount = round($user->bonusHistories()
                ->where('type', BonusTypeEnum::Accrual)
                ->where('status', BonusStatusEnum::Active)
                ->where('remaining_amount', '>', 0)
                ->where('expires_at', $closestExpiryDate)
                ->sum('remaining_amount'));

            $expireDate = Carbon::parse($closestExpiryDate);
            $daysLeft = max(0, $now->diffInDays($closestExpiryDate, false));
        }

        // Доступные бонусы = сумма remaining_amount активных начислений
        // Округляем до целого числа
        $bonusCount = round($user->bonusHistories()
            ->where('type', BonusTypeEnum::Accrual)
            ->where('status', BonusStatusEnum::Active)
            ->where('remaining_amount', '>', 0)
            ->where('active_date', '<=', $now)
            ->where(function ($query) use ($now) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', $now);
            })
            ->sum('remaining_amount'));


        $currentLevel = $user->loyaltyLevel?->loyaltyLevel;

        $levels = LoyaltyLevel::orderBy('coefficient', 'asc')->get();

        if ($levels->isEmpty()) {
            $pointsToNext = 0;
        } else {
            $durationDays = 90;

            // Сумма заказов за период (только выполненные заказы) - та же логика, что в LoyaltyService
            $totalSpent = $user->orders()
                ->where('status', OrderStatusEnum::Completed->value)
                ->where('created_at', '>=', $now->copy()->subDays($durationDays))
                ->where('created_at', '<=', $now)
                ->sum('price_final');

            $nextLevel = $levels->firstWhere('coefficient', '>', $totalSpent);

            $pointsToNext = $nextLevel ? max(0, ($nextLevel->coefficient - $totalSpent)) : 0;


        }

        $noveltyProducts = Product::where('is_active', true)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->orderBy('created_at', 'desc')
            ->get();
        $photoCategories = MainCategory::where('is_active',true)->orderBy('position','asc')->get();

        $notReadedAlertsCount = $user->alerts()->where('is_read', false)->where('user_deleted', false)->count();
        $notReadedNotificationsCount = $user->promoNotifications()->where('is_read', false)->where('user_deleted', false)->where('active_date', '<=', now())->count();
        $notReadedSum = $notReadedAlertsCount + $notReadedNotificationsCount;

        $privilegeBanners = Banner::where('is_active', true)
            ->where('location', BannerLocationEnum::PrivilegePage)
            ->orderBy('id')
            ->get()
            ->unique('id');

        return Inertia::render('profile/profile-privilege-program/ui/ProfilePrivilegeProgram', [
            'levels' => LoyaltyLevelResource::collection($levels),
            'currentLevel' => $currentLevel?->name,
            'allBonusCount' => (int) round($bonusCount),
            'oldestBonusCount' => (int) round($bonusesExpiringCount),
            'pointsToNext' => (int) round($pointsToNext),
            'expireDate' => $expireDate?->format('d.m.Y'),
            'daysLeft' => $daysLeft,
            'banners' => $privilegeBanners ? BannerResource::collection($privilegeBanners) : null,
            'noveltyProducts' => ProductCardResource::collection($noveltyProducts),
            'photoCategories' => MainCategoryResource::collection($photoCategories),
            'notReadedAlertsCount' => (int) $notReadedSum

        ]);
    }

    #[Endpoint('Страница уведомлений ')]
    #[BodyParam('category_ids', 'array', 'Массив ID категорий для фильтрации уведомлений', required: false, example: "[1, 2, 3]")]
    #[BodyParam('category_ids.*', 'integer', 'ID категории', required: false, example: 2)]
    public function notifications(Request $request)
    {
        $user = auth()->user();
        $categoryIds = $request->input('category_ids');

        $alerts = Alert::where('user_id', $user->id)
            ->where(function ($query) {
                $query->where('user_deleted', '!=', true)
                    ->orWhereNull('user_deleted');
            })
            ->orderBy('created_at', 'desc') // Уже есть
            ->get();

        $notificationsQuery = PromoNotification::where('user_id', $user->id)
            ->where(function ($query) {
                $query->where('user_deleted', '!=', true)
                    ->orWhereNull('user_deleted');
            })
            ->where('active_date', '<=', now())
            ->orderBy('created_at', 'desc'); // Добавлено сюда, до фильтрации по категориям

        if ($categoryIds && is_array($categoryIds) && !empty($categoryIds)) {
            $notificationsQuery->where(function ($query) use ($categoryIds) {
                $query->whereHas('product', function ($q) use ($categoryIds) {
                    $q->whereHas('categories', function ($q2) use ($categoryIds) {
                        $q2->whereIn('category_id', $categoryIds);
                    });
                })
                    ->orWhereHas('promotion', function ($q) use ($categoryIds) {
                        $q->whereHas('products', function ($q2) use ($categoryIds) {
                            $q2->whereHas('categories', function ($q3) use ($categoryIds) {
                                $q3->whereIn('category_id', $categoryIds);
                            });
                        });
                    });
            });
        }

        $notifications = $notificationsQuery->get(); // Убрано orderBy('created_at', 'desc'), так как уже добавлено выше

        $notReadedAlertsCount = $user->alerts()->where('is_read', false)->where('user_deleted', false)->count();
        $notReadedNotificationsCount = $user->promoNotifications()->where('is_read', false)->where('user_deleted', false)->where('active_date', '<=', now())->count();
        $notReadedSum = $notReadedAlertsCount + $notReadedNotificationsCount;

        $notificationsCount = $user->promoNotifications()->where('user_deleted', false)->where('active_date', '<=', now())->count();

        return Inertia::render('profile/profile-notifications/ui/ProfileNotificationsPage',[
            'alerts' => AlertResource::collection($alerts),
            'notifications' => NotificationResource::collection($notifications),
            'notReadedAlertsCount' => (int) $notReadedSum,
            'notificationsCount' => (int) $notificationsCount,
        ]);
    }

    #[Endpoint("Страница истории бонусов", "Возвращает историю начислений/списания бонусов.")]
    public function bonusHistory(){

        $user = auth()->user();
        $now = \Carbon\Carbon::now();

        $bonusHistory = $user->bonusHistories()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        // Начисления: ВСЕ начисления (Active, Spent, Expired - неважно)
        $accrualHistory = $bonusHistory->where('type', BonusTypeEnum::Accrual)
            ->where('active_date', '<=', $now);

        // Списания: ВСЕ списания (траты + сгорания)
        $withdrawalHistory = $bonusHistory->where('type', BonusTypeEnum::Withdrawal);

        // В обработке: ожидающие активации (active_date в будущем)
        $processingHistory = $bonusHistory->where('type', BonusTypeEnum::Accrual)
            ->where('active_date', '>', $now);

        $notReadedAlertsCount = $user->alerts()->where('is_read', false)->where('user_deleted', false)->count();
        $notReadedNotificationsCount = $user->promoNotifications()->where('is_read', false)->where('user_deleted', false)->where('active_date', '<=', now())->count();
        $notReadedSum = $notReadedAlertsCount + $notReadedNotificationsCount;

        return Inertia::render('profile/profile-privilege-history/ui/ProfilePrivilegeHistoryPage', [
            'bonusHistory' => BonusHistoryResource::collection($bonusHistory),
            'accrualHistory' => BonusHistoryResource::collection($accrualHistory),
            'withdrawalHistory' => BonusHistoryResource::collection($withdrawalHistory),
            'processingHistory' => BonusHistoryResource::collection($processingHistory),
            'notReadedAlertsCount' => (int) $notReadedSum

        ]);

    }

    #[Endpoint('Страница редактирования')]
    public function edit()
    {
        $user = Auth::user();
        $userAddresses = $user->addresses;

        $notReadedAlertsCount = $user->alerts()->where('is_read', false)->where('user_deleted', false)->count();
        $notReadedNotificationsCount = $user->promoNotifications()->where('is_read', false)->where('user_deleted', false)->where('active_date', '<=', now())->count();
        $notReadedSum = $notReadedAlertsCount + $notReadedNotificationsCount;

        $allContactMethods = ContactMethod::get(['id', 'name']);

        $userContactMethods = $user->contactMethods()->pluck('contact_methods.id');
        $allDeliveryZones = DeliveryZone::get('id','name');

        return Inertia::render('profile/profile-edit/ui/ProfileEdit', [
            'userAddresses'       => AddressResource::collection($userAddresses),
            'notReadedAlertsCount'=> (int) $notReadedSum,
            'allContactMethods'   => $allContactMethods,
            'userContactMethods'  => $userContactMethods,
            'allDeliveryZones'  => $allDeliveryZones,
        ]);
    }

    #[Endpoint("Получение истории заказов пользователя", "Возвращает список заказов пользователя с возможностью сортировки по дате, статусу или номеру заказа.")]
    #[QueryParam("sort_by", "string", "Поле для сортировки. Возможные значения: created_at, status, code.", required: false, example: "status")]
    #[QueryParam("sort_dir", "string", "Направление сортировки: asc или desc.", required: false, example: "asc")]
    #[ResponseFromApiResource(OrderProfileCardResource::class, Order::class, collection: true, paginate: false)]
    public function ordersHistory(Request $request)
    {
        $user = auth()->user();

        $allowedSortFields = [
            'created_at',
            'status',
            'code',
        ];

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');

        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'created_at';
        }

        if (!in_array(strtolower($sortDir), ['asc', 'desc'])) {
            $sortDir = 'desc';
        }
        $query = $user->orders();
        if ($sortBy === 'status') {
            $query->orderBy('status', $sortDir);
        } else {
            if ($sortBy == 'code'){
                $query->orderBy('id', $sortDir);
            }else{
                $query->orderBy($sortBy, $sortDir);

            }
        }
        $userOrders = $query->get();

        $notReadedAlertsCount = $user->alerts()->where('is_read', false)->where('user_deleted', false)->count();
        $notReadedNotificationsCount = $user->promoNotifications()->where('is_read', false)->where('user_deleted', false)->where('active_date', '<=', now())->count();
        $notReadedSum = $notReadedAlertsCount + $notReadedNotificationsCount;

        return Inertia::render('profile/profile-orders-history/ui/ProfileOrdersHistory', [
            'userOrders' => OrderProfileCardResource::collection($userOrders),
            'notReadedAlertsCount' => (int) $notReadedSum
        ]);
//        return [
//            'userOrders' => OrderProfileCardResource::collection($userOrders),
//            'notReadedAlertsCount' => (int) $notReadedSum
//        ];
    }

    #[Endpoint("Детальная страница заказа", "Возвращает подробную информацию о заказе.")]
    public function ordersHistoryShow(Request $request, Order $order)
    {
        $order->load([
            'items.item',
            'deliveryAddress.city',
            'deliveryAddress.deliveryZone',
            'pickupLocation'
        ]);

        $user = auth()->user();
        $notReadedAlertsCount = $user->alerts()->where('is_read', false)->where('user_deleted', false)->count();
        $notReadedNotificationsCount = $user->promoNotifications()->where('is_read', false)->where('user_deleted', false)->where('active_date', '<=', now())->count();
        $notReadedSum = $notReadedAlertsCount + $notReadedNotificationsCount;

        return Inertia::render('profile/profile-order/ui/ProfileOrder',[
            'order' => new OrderProfileDetailResource($order),
            'notReadedAlertsCount' => (int) $notReadedSum
        ]);
//        return [
//            'order' => new OrderProfileDetailResource($order),
//            'notReadedAlertsCount' => (int) $notReadedSum
//        ];
    }

    #[Endpoint('Страница избранного ')]
    public function whiteList(Request $request)
    {
        $user = auth()->user();

        // Безопасно получаем whiteList
        $whiteList = $user->whiteList;

        if ($whiteList) {
            $whiteListProducts = $whiteList->items()
                ->where('item_type', Product::class)
                ->with(['item' => fn($q) => $q->active()])
                ->get()
                ->map->item
                ->filter()
                ->values();

            $whiteListRecipes = $whiteList->items()
                ->where('item_type', Recipe::class)
                ->with('item')
                ->get()
                ->map->item
                ->filter()
                ->values();
        } else {
            $whiteListProducts = collect();
            $whiteListRecipes = collect();
        }

        $productWhiteListBanner = Banner::where('is_active', true)
            ->where('banner_type', BannerTypeEnum::ProductWhiteList)
            ->get();

        $recipeWhiteListBanner = Banner::where('is_active', true)
            ->where('banner_type', BannerTypeEnum::RecipeWhiteList)
            ->get();

        $notReadedAlertsCount = $user->alerts()->where('is_read', false)->where('user_deleted', false)->count();
        $notReadedNotificationsCount = $user->promoNotifications()->where('is_read', false)->where('user_deleted', false)->where('active_date', '<=', now())->count();
        $notReadedSum = $notReadedAlertsCount + $notReadedNotificationsCount;

        return Inertia::render('profile/profile-favorite/ui/ProfileFavorite', [
            'productWhiteListBanner' => BannerResource::collection($productWhiteListBanner),
            'recipeWhiteListBanner' => BannerResource::collection($recipeWhiteListBanner),
            'products' => ProductCardResource::collection($whiteListProducts),
            'recipes' => RecipeCardResource::collection($whiteListRecipes),
            'notReadedAlertsCount' => (int) $notReadedSum
        ]);
    }


    #[Endpoint('Страница отзывов')]
    public function reviews(Request $request)
    {
        $user = auth()->user();

        $orderedItems = $user->orders()
            ->where('status', OrderStatusEnum::Completed->value)
            ->with('items.item')
            ->get()
            ->pluck('items.*.item')
            ->flatten()
            ->filter()
            ->unique(fn($item) => get_class($item) . ':' . $item->id)
            ->values();

        // избранные рецепты
        $favRecipes = $user->whiteList
            ? $user->whiteList->recipes()->with('item')->get()->pluck('item')->filter()->values()
            : collect();

        // все элементы которые могут быть оценены или не оценены
        $allItems = $orderedItems->merge($favRecipes)
            ->unique(fn($item) => get_class($item) . ':' . $item->id)
            ->values();

        // отзывы пользователя на $allItems
        $reviews = Review::whereIn('item_id', $allItems->pluck('id'))
            ->whereIn('item_type', $allItems->map(fn($i) => get_class($i))->unique())
            ->where('user_id', $user->id)
            ->get()
            ->groupBy(fn($r) => $r->item_type . ':' . $r->item_id)
            ->map(fn($group) => $group->sortByDesc('id')->first());

        // ждет ли товар(рецепт/комбо) отзыва
        $isPending = fn($item) =>
            !isset($reviews[get_class($item) . ':' . $item->id]) ||
            $reviews[get_class($item) . ':' . $item->id]->status == ReviewStatusEnum::UserDeleted;

        $allowedSortFields = [
            'created_at',
        ];

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');

        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'created_at';
        }

        if (!in_array(strtolower($sortDir), ['asc', 'desc'])) {
            $sortDir = 'desc';
        }

//        $productsPendingReview = $orderedItems->filter(fn($item) => $item instanceof \Product\Models\Product && $isPending($item));
        $productsPendingReview = $user->orders()
            ->where('status', OrderStatusEnum::Completed->value)
            ->with('items.item')
            ->get()
            ->pluck('items.*')
            ->flatten()
            ->filter(fn($orderItem) => ($orderItem->item instanceof \Product\Models\Product
                    || $orderItem->item instanceof \Combo\Models\Combo)
                && $isPending($orderItem->item))
            ->values();

        $productsReviewed = collect($reviews)->filter(fn($r) => $r->item instanceof \Product\Models\Product && $r->status != ReviewStatusEnum::UserDeleted || $r->item instanceof \Combo\Models\Combo && $r->status != ReviewStatusEnum::UserDeleted)
            ->sortBy([
                [$sortBy, $sortDir],
            ])
            ->values();

        $recipesPendingReview = $favRecipes->filter(fn($item) => $item instanceof \Recipe\Models\Recipe && $isPending($item));
        $recipesReviewed = collect($reviews)->filter(fn($r) => $r->item instanceof \Recipe\Models\Recipe && $r->status != ReviewStatusEnum::UserDeleted)
            ->sortBy([
                [$sortBy, $sortDir],
            ])
            ->values();


        $notReadedAlertsCount = $user->alerts()->where('is_read', false)->where('user_deleted', false)->count();
        $notReadedNotificationsCount = $user->promoNotifications()->where('is_read', false)->where('user_deleted', false)->where('active_date', '<=', now())->count();
        $notReadedSum = $notReadedAlertsCount + $notReadedNotificationsCount;


        $reviewBonusValue = Setting::where('key', 'review_bonus')->first()?->value ?? 0;


        return Inertia::render('profile/profile-review/ui/ProfileReview', [
            'productsPendingReview' => OrderItemResource::collection($productsPendingReview),
            'productsReviewed'      => ReviewResource::collection($productsReviewed),
            'recipesPendingReview'  => RecipeCardResource::collection($recipesPendingReview),
            'recipesReviewed'       => ReviewResource::collection($recipesReviewed),
            'notReadedAlertsCount' => (int) $notReadedSum,
            'reviewBonusAmount' => $reviewBonusValue
        ]);
    }






    #[Endpoint('Страница настроек уведомлений ')]
    public function settings(Request $request)
    {
        $user = auth()->user();

        $userSetting = $user->setting;


        $notReadedAlertsCount = $user->alerts()->where('is_read', false)->where('user_deleted', false)->count();
        $notReadedNotificationsCount = $user->promoNotifications()->where('is_read', false)->where('user_deleted', false)->where('active_date', '<=', now())->count();
        $notReadedSum = $notReadedAlertsCount + $notReadedNotificationsCount;

        return Inertia::render('profile/profile-settings/ui/ProfileSettings', [
            'userSetting' => new UserSettingResource($userSetting),
            'notReadedAlertsCount' => (int) $notReadedSum
        ]);
    }

    #[Endpoint('Обновить настройки пользователя')]
    #[BodyParam('comment_notifications', 'Включить уведомления о комментариях', 'boolean', required: true, example: true)]
    #[BodyParam('sale_notifications', 'Включить уведомления о скидках', 'boolean', required: true, example: true)]
    #[BodyParam('email_notifications', 'Включить уведомления по e-mail', 'boolean', required: true, example: true)]
    #[BodyParam('sms_notifications', 'Включить SMS-уведомления', 'boolean', required: true, example: false)]
    #[BodyParam('messenger_notifications', 'Включить уведомления в мессенджере', 'boolean', required: true, example: false)]
    #[BodyParam('favorite_categories', 'Сообщать о новых товарах из любимых категорий', 'boolean', required: true, example: true)]
    #[BodyParam('often_type', 'Частота уведомлений', 'string', required: false, example: 'daily', enum: ['daily', 'weekly', 'sales_only'])]
    #[BodyParam('favorite_categories_list', 'Список ID любимых категорий', 'array', required: false, example: [1,3,5])]
    #[BodyParam('favorite_categories_list.*', 'ID категории', 'integer', required: false, example: 1)]
    #[Response(description: 'Настройки обновлены',status: 200)]
    public function settingsUpdate(Request $request)
    {
        $user = auth()->user();
        $userSetting = $user->setting;

        $validated = $request->validate([
            'comment_notifications' => 'required|boolean',
            'sale_notifications' => 'required|boolean',
            'email_notifications' => 'required|boolean',
            'sms_notifications' => 'required|boolean',
            'messenger_notifications' => 'required|boolean',
            'favorite_categories' => 'required|boolean',
            'often_type' => ['nullable', 'string', Rule::in(array_keys(\App\Modules\UserSetting\src\Enums\OftenTypeEnum::toArray()))],
            'favorite_categories_list' => 'array',
            'favorite_categories_list.*' => 'integer|exists:categories,id',
        ]);

        $userSetting->update([
            'comment_notifications' => $validated['comment_notifications'],
            'sale_notifications' => $validated['sale_notifications'],
            'email_notifications' => $validated['email_notifications'],
            'sms_notifications' => $validated['sms_notifications'],
            'messenger_notifications' => $validated['messenger_notifications'],
            'favorite_categories' => $validated['favorite_categories'],
            'often_type' => $validated['often_type'] ?? null,

        ]);
        if ($validated['favorite_categories']) {
            $userSetting->favoriteCategories()->sync($validated['favorite_categories_list'] ?? []);
        } else {
            $userSetting->favoriteCategories()->detach();
        }
        $subscription = Subscription::where('user_id', $user->id)->first();
        if ($subscription) {
            $subscription->update([
                'is_active' => $validated['email_notifications'],
            ]);
        } else {
            Subscription::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'is_active' => $validated['email_notifications'],
                'is_bonus_expired' => $validated['email_notifications'] ? 0 : 1,
            ]);
        }

        return redirect()->back()->with('success', 'Настройки обновлены');
    }



    #[Endpoint("Обновить данные профиля", "Изменение личных данных и адресов пользователя")]
    #[BodyParam("name", "string", "Имя", required: true, example: "Иван")]
    #[BodyParam("last_name", "string", "Фамилия", required: true, example: "Иванов")]
    #[BodyParam("second_name", "string", "Отчество", required: false, example: "Иванович")]
    #[BodyParam("gender", "string", "Пол (значение из GenderEnum)", required: false, example: "man/woman")]
    #[BodyParam("birth_date", "date", "Дата рождения в формате d.m.Y", required: false, example: "01.01.1990")]
    #[BodyParam("phone", "string", "Телефон", required: false, example: "+79998887766")]
    #[BodyParam("phone_additional", "string", "Дополнительный телефон", required: false)]
    #[BodyParam("email", "string", "Email", required: false, example: "test@example.com")]
    #[BodyParam("password", "string", "Новый пароль (если требуется сменить)", required: false)]
    #[BodyParam("password_confirmation", "string", "Подтверждение пароля", required: false)]
    #[BodyParam("addresses", "array", "Список адресов", required: false)]
    #[BodyParam("addresses.postal_code", "string", "Почтовый индекс", required: false,example: "344001")]
    #[BodyParam("addresses.region", "string", "Регион", required: false,example: "Ростовская область")]
    #[BodyParam("addresses.city_dadata", "string", "Город(нас.пункт)", required: false,example: "Ростов-на-Дону")]
    #[BodyParam("addresses.city_id", "integer", "ID города", required: false,example: 1)]
    #[BodyParam("addresses.street", "string", "Улица", required: false,example: "Пушкина")]
    #[BodyParam("addresses.house", "string", "Дом", required: false,example: "12")]
    #[BodyParam("addresses.entrance", "string", "Подъезд", required: false,example: "8")]
    #[BodyParam("addresses.floor", "string", "Этаж", required: false,example: "5")]
    #[BodyParam("addresses.apartment", "string", "Квартира", required: false,example: "25")]
    #[BodyParam("addresses.car_pass", "boolean", "Пропуск для авто", required: false,example:true)]
    #[BodyParam("addresses.delivery_zone_id", "integer", "зона доставки", required: false,example:'1')]
    #[BodyParam("addresses.is_primary", "boolean", "Является основным адресом", required: false,example: true)]
    #[BodyParam("addresses.value_dadata", "string", "Город улица номер дома", required: true)]
    #[BodyParam("contact_methods", "array", "Список контактных методов пользователя (id из таблицы contact_methods)", required: false, example: [1, 2, 3])]
    #[BodyParam("contact_methods.*", "integer", "ID контактного метода", required: false, example: 1)]
    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:50',
            'second_name' => 'nullable|string|max:50',//отчество
            'last_name' => 'required|string|max:50',//фамилия
            'gender' => ['nullable', Rule::in(array_keys(GenderEnum::toArray()))],
            'birth_date' => 'nullable|date',
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'phone_additional' => 'nullable|string|max:20',
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'contact_methods' => 'nullable|array',
            'contact_methods.*' => 'exists:contact_methods,id',
            'addresses' => 'nullable|array',
            'addresses.*.postal_code' => 'nullable|string|max:20',
            'addresses.*.region' => 'nullable|string|max:255',
            'addresses.*.city_id' => 'nullable|exists:cities,id',
            'addresses.*.delivery_zone_id' => 'nullable|exists:delivery_zones,id',
            'addresses.*.city_dadata' => 'nullable|string',
            'addresses.*.street' => 'nullable|string|max:255',
            'addresses.*.house' => 'nullable|string|max:50',
            'addresses.*.entrance' => 'nullable|string|max:50',
            'addresses.*.floor' => 'nullable|string|max:50',
            'addresses.*.apartment' => 'nullable|string|max:50',
            'addresses.*.car_pass' => 'boolean',
            'addresses.*.is_primary' => 'boolean',
            'addresses.*.value_dadata' => 'required|string',
        ]);

        $phone = \App\Helpers\PhoneHelper::formatPhone($request->phone);
        $phone = $phone ?: null;


        if ($request->filled('birth_date')) {
            $birthDate = Carbon::parse($request->birth_date)->format('Y-m-d');
        }
        $data = [
            'name' => $request->name,
            'second_name' => $request->second_name,//отчество
            'last_name' => $request->last_name,//фамилия
            'gender' => $request->gender,
            'birth_date' => $birthDate ?? null,
            'phone' => $phone ?? null,
            'phone_additional' => $request->phone_additional,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $emailChangeMessage = '';

        if ($request->email !== $user->email) {
            // Сброс верификации почты и отправка письма
            $data['email_verified_at'] = null;
            $emailChangeMessage = ' Отправлено письмо на ваш email. Не забудьте проверить папку СПАМ';
        }

        $user->update($data);

        if ($emailChangeMessage) {
            $user->sendEmailVerificationNotification();
        }
        // Сохранение адресов
        if ($request->filled('addresses')) {
          $addresses = $request->addresses;

          foreach ($addresses as &$address) {
              if (!empty($address['value_dadata'])) {
                  if (preg_match('/^(.*?),\s*д\s*(.+)$/ui', $address['value_dadata'], $matches)) {
                      $address['street'] = trim($matches[1]);
                      $address['house']  = trim($matches[2]);
                  } else {
                      $address['street'] = $address['value_dadata'];
                  }
              }
          }

          $this->saveAddresses($user, $addresses);
        }

        if ($request->filled('contact_methods')) {
            $user->contactMethods()->sync($request->contact_methods);
        } else {
            $user->contactMethods()->sync([]);
        }
        return redirect()->route('user.profile.index')->with('success', 'Данные были успешно сохранены' . $emailChangeMessage);
    }


    #[Endpoint("Изменить пароль", "Смена пароля пользователя с проверкой старого пароля")]
    #[BodyParam("old_password", "string", "Старый пароль", required: false, example: "oldPass123")]
    #[BodyParam("password", "string", "Новый пароль", required: true, example: "newPass123")]
    #[BodyParam("password_confirmation", "string", "Подтверждение нового пароля", required: true, example: "newPass123")]
    public function passwordChange(Request $request)
    {
        $user = auth()->user();

        $hasPassword = !empty($user->password);

        $rules = [
            'password' => ['required', 'confirmed'],
        ];

        if ($hasPassword) {
            $rules['old_password'] = ['required'];
        }

        $request->validate($rules);

        if ($hasPassword && !Hash::check($request->old_password, $user->password)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'old_password' => ['Старый пароль введён неверно.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Готово. Новый пароль успешно сохранён.');
    }



    #[Endpoint("Выйти из системы", "Разлогин пользователя и сброс сессии")]
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    #[Endpoint("Страница удаления аккаунта", "Возвращает страницу удаления аккаунта.")]
    public function deletePage(){
        $user = auth()->user();

        $notReadedAlertsCount = $user->alerts()->where('is_read', false)->where('user_deleted', false)->count();
        $notReadedNotificationsCount = $user->promoNotifications()->where('is_read', false)->where('user_deleted', false)->where('active_date', '<=', now())->count();
        $notReadedSum = $notReadedAlertsCount + $notReadedNotificationsCount;

        return Inertia::render('profile/profile-delete/ui/ProfileDelete',[
            'notReadedAlertsCount' => (int) $notReadedSum
        ]);
    }

    #[Endpoint("Удалить аккаунт", "Отметить аккаунт как удалённый и выйти из системы")]
    #[Authenticated]
    #[BodyParam("reason", "string", required: true, description: "Причина удаления. Возможные значения: have_any_account, data_security, lots_of_ads, another.")]
    #[BodyParam("comment", "string", required: false, description: "Комментарий (обязателен, если причина — another).")]
    #[BodyParam("password", "string", required: true, description: "Текущий пароль пользователя для подтверждения удаления.")]
    public function delete(Request $request)
    {
        $user = auth()->user();

        $hasPassword = !empty($user->password);

        $rules = [
            'reason'  => ['required', Rule::in(array_keys(DeleteReasonEnum::toArray()))],
            'comment' => ['nullable', 'string', 'required_if:reason,another'],
        ];

        if ($hasPassword) {
            $rules['password'] = ['required', 'string'];
        }

        $request->validate($rules, [
            'comment.required_if' => 'Поле комментарий обязательно для заполнения, когда выбрана причина "другое".',
        ]);

        // проверяем пароль только если он есть
        if ($hasPassword && !Hash::check($request->password, $user->password)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'password' => 'Старый пароль неверный',
            ]);
        }

        DeletedUser::create([
            'user_id' => $user->id,
            'reason'  => $request->reason,
            'comment' => $request->comment,
        ]);

        $user->update([
            'phone_deleted' => $user->phone,
            'email_deleted' => $user->email,
            'phone' => null,
            'email' => null,
            'is_self_deleted' => true,
            'is_archived' => true,
            'vk_id' => null,
            'yandex_id' => null,
        ]);

        Auth::logout();
        $request->session()->invalidate();

        return redirect()->route('main-page')->with('success', 'Аккаунт успешно удалён');
    }



    private function saveAddresses($user, array $addresses)
    {
        $incomingIds = array_column($addresses, 'id');

        $user->addresses()
            ->whereNotIn('id', $incomingIds)
            ->update(['user_deleted' => true]);

        // активные адреса
        $existingActiveAddresses = $user->addresses()->where('user_deleted', false)->count();

        // проверяем есть ли в новых адресах is_primary = true
        $hasPrimaryInIncoming = collect($addresses)->contains(fn($a) => !empty($a['is_primary']));
        foreach ($addresses as $index => $addr) {
            $finalAddress = Address::formatFinalAddress($addr);

            // если это первый добавляемый адрес и вообще нет активных, то делаем его основным
            if ($existingActiveAddresses === 0 && !$hasPrimaryInIncoming && $index === 0) {
                $addr['is_primary'] = true;
            }
            if (!empty($addr['id'])) {
                $user->addresses()->updateOrCreate(
                    ['id' => $addr['id']],
                    [
                        'dadata_json' => $addr['dadata_json'] ?? null,
                        'postal_code' => $addr['postal_code'] ?? null,
                        'region' => $addr['region'] ?? null,
                        'city_dadata' => $addr['city_dadata'] ?? null,
                        'city_id' => $addr['city_id'] ?? null,
                        'street' => $addr['street'] ?? null,
                        'house' => $addr['house'] ?? null,
                        'entrance' => $addr['entrance'] ?? null,
                        'floor' => $addr['floor'] ?? null,
                        'delivery_zone_id' => $addr['delivery_zone_id'] ?? null,
                        'apartment' => $addr['apartment'] ?? null,
                        'car_pass' => $addr['car_pass'] ?? false,
                        'is_primary' => $addr['is_primary'] ?? false,
                        'value_dadata' => $addr['value_dadata'] ?? null,
                        'final_address' => $finalAddress,
                        'user_deleted' => false,
                    ]
                );
            } else {
                $user->addresses()->create([
                    'dadata_json' => $addr['dadata_json'] ?? null,
                    'postal_code' => $addr['postal_code'] ?? null,
                    'region' => $addr['region'] ?? null,
                    'city_dadata' => $addr['city_dadata'] ?? null,
                    'city_id' => $addr['city_id'] ?? null,
                    'street' => $addr['street'] ?? null,
                    'house' => $addr['house'] ?? null,
                    'entrance' => $addr['entrance'] ?? null,
                    'floor' => $addr['floor'] ?? null,
                    'apartment' => $addr['apartment'] ?? null,
                    'car_pass' => $addr['car_pass'] ?? false,
                    'is_primary' => $addr['is_primary'] ?? false,
                    'value_dadata' => $addr['value_dadata'] ?? null,
                    'final_address' => $finalAddress,
                    'user_deleted' => false,
                    'delivery_zone_id' => $addr['delivery_zone_id'] ?? null,
                ]);
            }
        }
    }

}
