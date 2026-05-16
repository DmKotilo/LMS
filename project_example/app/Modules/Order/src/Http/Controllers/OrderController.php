<?php

namespace Order\Http\Controllers;

use Address\Http\Resources\AddressResource;
use Address\Model\Address;
use Address\Model\ContactMethod;
use App\Http\Controllers\Controller;
use App\Modules\Banner\src\Http\Enums\BannerLocationEnum;
use App\Modules\Banner\src\Http\Resources\BannerResource;
use App\Modules\Cart\src\Traits\GetCart;
use App\Modules\Cart\src\Services\CartFormattedItemsService;
use App\Modules\Cart\src\Services\FirstOrderGiftService;
use App\Support\UserBlockHelper;
use App\Modules\Delivery\src\Http\Resources\PickupLocationResource;
use App\Modules\Product\Review\src\Http\Resources\ReviewHomeCardResource;
use App\Modules\Referral\src\Services\ReferralService;
use Banner\Models\Banner;
use Bonus\Services\BonusService;
use App\Notifications\WelcomeNewUserNotification;
use Combo\Models\Combo;
use Delivery\Models\DeliveryZone;
use Delivery\Models\PickupLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use Order\Http\Requests\OrderCalculateFormRequest;
use Order\Http\Requests\OrderStoreFormRequest;
use Order\Http\Resources\TransactionMethodResource;
use Order\Models\Order;
use Order\Models\OrderItem;
use Order\Models\OrderPromocode;
use Order\Models\TransactionMethod;
use Order\Services\OrderService;
use Order\Services\OrderBitrixService;
use Order\Enums\OrderPromocodeStatusEnum;
use Product\Models\Product;
use Review\Models\ProductReview;
use User\Models\User;
use Illuminate\Validation\ValidationException as LaravelValidationException;
use App\Helpers\PhoneHelper;
use App\Http\Middleware\CaptureUtmParameters;
use App\Support\UtmHelper;

#[Group("Заказы")]
class OrderController extends Controller
{
    use GetCart;


    #[Endpoint("Оформить заказ", "Создание нового заказа из корзины или по одному товару (быстрая покупка).")]
    #[BodyParam("product_id", "integer", "ID товара. Если указан — заказ оформляется по одному товару, корзина не очищается.", required: false, example: 15)]
    #[BodyParam("combo_id", "integer", "ID Комбо. Если указан — заказ оформляется по одному товару, корзина не очищается.", required: false, example: 15)]
    #[BodyParam("address_id", "integer", "ID адреса пользователя.", required: false, example: 3)]
    #[BodyParam("transaction_method_id", "integer", "ID способа оплаты.", required: true, example: 1)]
    #[BodyParam("contact_methods", "array", "Список контактных методов пользователя (id из таблицы contact_methods)", required: false, example: [1, 2, 3])]
    #[BodyParam("contact_methods.*", "integer", "ID контактного метода", required: false, example: 1)]
    #[BodyParam("last_name", "string", "Фамилия получателя.", required: true, example: "Петров")]
    #[BodyParam("name", "string", "Имя получателя.", required: true, example: "Иван")]
    #[BodyParam("second_name", "string", "Отчество получателя.", required: false, example: "Сергеевич")]
    #[BodyParam("phone", "string", "Телефон получателя.", required: true, example: "+79995553322")]
    #[BodyParam("email", "string", "Email получателя.", required: true, example: "ivan.petrov@example.com")]
    #[BodyParam("comment", "string", "Комментарий к заказу.", required: false, example: "Позвоните за час до доставки")]
    #[BodyParam("promo", "string", "Промокод.", required: false, example: "SALE2025")]
    #[BodyParam("delivery_time_from", "string", "Интервал доставки ОТ", required: false)]
    #[BodyParam("delivery_time_to", "string", "Интервал доставки ДО", required: false,)]
    #[BodyParam("delivery_date", "string", "Дата доставки в формате YYYY-MM-DD", required: false)]
    #[BodyParam("delivery_type", "string", "Способ доставки", required: true, example: "courier || pickup")]
    #[Response(["success" => "Заказ оформлен"], description: "Успешное оформление заказа")]
    public function store(
        OrderStoreFormRequest $request,
        ReferralService $referralService,
        FirstOrderGiftService $firstOrderGiftService
    )
    {
        $data = $request->validated();
        $data['delivery_date'] = $this->normalizeDeliveryDate($data['delivery_date'] ?? null);
        $useExpiringBonuses = (bool) ($data['use_expiring_bonuses'] ?? false);
        $utm = UtmHelper::extract($request);

        // Форматируем номер телефона
        if (!empty($data['phone'])) {
            $data['phone'] = PhoneHelper::formatPhone($data['phone']);
        }

        $cart = $this->getCart($request);

        $user = Auth::user();

        $blockedPayload = [
            'blocked' => [UserBlockHelper::blockMessage()],
        ];

        if (UserBlockHelper::isBlocked($user)) {
            throw LaravelValidationException::withMessages($blockedPayload);
        }

        if (!$user) {
            $user = User::where('email', $data['email'])->first();

            if (UserBlockHelper::isBlocked($user)) {
                throw LaravelValidationException::withMessages($blockedPayload);
            }

            if (!$user && !empty($data['phone'])) {
                $user = User::where('phone', $data['phone'])->first();

                if (UserBlockHelper::isBlocked($user)) {
                    throw LaravelValidationException::withMessages($blockedPayload);
                }
            }

            if (!$user) {
                $password = \Illuminate\Support\Str::random(10);

                $user = User::create([
                    'name'        => $data['name'],
                    'last_name'   => $data['last_name'],
                    'second_name' => $data['second_name'] ?? null,
                    'email'       => $data['email'],
                    'phone'       => $data['phone'],
                    'password'    => Hash::make($password),
                ]);

                $user->notify(new WelcomeNewUserNotification($user, $password));

            }
        }
        Auth::login($user);
        $user = Auth::user();
        $data['user_id'] = $user->id;

        if ($cart->user_id !== $user->id) {
            $cart->update([
                'user_id' => $user->id,
                'session_id' => $request->session()->getId(),
            ]);
        }
        $isQuickBuy = !empty($data['product_id']) || !empty($data['combo_id']);

        if (!$isQuickBuy) {
            $firstOrderGiftService->syncGiftForCart($cart, $user, $data['email'] ?? null, $data['phone'] ?? null);
            $cart->refresh();
        }

        $cartItems = OrderService::getOrderCollect(
            $cart,
            product_id: $data['product_id'] ?? null,
            combo_id: $data['combo_id'] ?? null
        );

        if ($cartItems->isEmpty()) {
            return redirect()->back()->with('error','Корзина пуста. Добавьте хотя бы один товар для оформления заказа.');
        }

        $cartItems = $firstOrderGiftService->appendGiftToCollection($cartItems, $user, $data['email'] ?? null, $data['phone'] ?? null);

        $promocode = null;
        if (!empty($data['promo'])) {
            $promocode = OrderPromocode::where('promocode', $data['promo'])->first();

            // Сначала проверяем валидность промокода БЕЗ увеличения счетчика
            if(!$promocode || $promocode->checkValidated(false) != true) {
                throw LaravelValidationException::withMessages(['error' => ['Промокод недействителен или превышен лимит использований.']]);
            }

            // Дополнительная проверка лимита перед созданием заказа
            if($promocode->exceeded_limit !== null && $promocode->number_applications >= $promocode->exceeded_limit) {
                throw LaravelValidationException::withMessages(['error' => ['Промокод недействителен или превышен лимит использований.']]);
            }

            // ✅ Присваиваем ID промокода заказу
            $data['order_promocode_id'] = $promocode->id;
            $data['promo'] = $promocode->promocode;
        }

        $address = Address::find($data['address_id'] ?? null);
        $data['delivery_address_id'] = $address ? $address->id : null;

        $bonusService = app(BonusService::class);
        $availableBonus = $bonusService->getAvailableBalance($user);

        $expiringBonusAmount = null;
        if ($useExpiringBonuses && $user) {
            $now = \Carbon\Carbon::now();
            $closestExpiryDate = $user?->bonusHistories()
                ?->where('type', \App\Modules\Bonus\Bonus\src\Enums\BonusTypeEnum::Accrual)
                ?->where('status', \App\Modules\Bonus\Bonus\src\Enums\BonusStatusEnum::Active)
                ?->where('remaining_amount', '>', 0)
                ?->where('active_date', '<=', $now)
                ?->whereNotNull('expires_at')
                ?->where('expires_at', '>', $now)
                ?->orderBy('expires_at', 'asc')
                ?->value('expires_at');

            if ($closestExpiryDate) {
                $expiringBonusAmount = $user?->bonusHistories()
                    ?->where('type', \App\Modules\Bonus\Bonus\src\Enums\BonusTypeEnum::Accrual)
                    ?->where('status', \App\Modules\Bonus\Bonus\src\Enums\BonusStatusEnum::Active)
                    ?->where('remaining_amount', '>', 0)
                    ?->where('expires_at', $closestExpiryDate)
                    ?->sum('remaining_amount');
            }
        }

        $orderCalculate = OrderService::calculateOrder(
            collectCartItems: $cartItems,
            promocode: $promocode ?? null,
            deliveryZone: $address?->deliveryZone,
            deliveryType: $data['delivery_type'] ?? null,
            useExpiringBonuses: $useExpiringBonuses,
            expiringBonusAmount: $expiringBonusAmount,
            availableBonus: $availableBonus
        );

        // Проверяем доступность товаров ПЕРЕД созданием заказа
        // Исключаем подарки из проверки, так как они могут быть недоступны
        $availableItems = collect($cartItems)->filter(function($cartItem) {
            return $cartItem->is_available && empty($cartItem->is_gift);
        });

        if ($availableItems->isEmpty()) {
            return redirect()->back()->with('error', 'Все товары в корзине недоступны. Пожалуйста, обновите корзину.');
        }

        $data = array_merge($data, $orderCalculate['order_detail']);
        $data['bonus_spent'] = $orderCalculate['order_detail']['bonus_spent'];
        $data['status'] = 'new';
        $order = Order::create($data);

        $request->session()->forget('applied_promocode');

        if ($order->bonus_spent > 0) {
            try {
                $bonusService->withdrawBonus(
                    user: $user,
                    amount: $order->bonus_spent,
                    isManual: false,
                    reason: null,
                    adminId: null,
                    comment: "Списание бонусов при оформлении заказа #{$order->id}",
                    order: $order
                );
            } catch (\Exception $e) {
                $order->delete();
                throw LaravelValidationException::withMessages(['error' => [$e->getMessage()]]);
            }
        }

        if (isset($data['contact_methods'])) {
            $order->contactMethods()->sync($data['contact_methods']);
        }

        $itemsCreated = 0;
        foreach ($cartItems as $cartItem) {
            // пропускаем недоступные товары
            if (!$cartItem->is_available) {
                continue;
            }

            $item = $cartItem->item;
            $quantity = max(1, (int) $cartItem->quantity);
            $isGift = !empty($cartItem->is_gift);

            // Для подарков цена всегда 0
            $itemPrice = $isGift ? 0 : $cartItem->total_price;
            $itemPriceWithoutSale = $isGift ? 0 : $cartItem->total_price_without_sale;
            $itemPriceForUnit = $isGift ? 0 : ($quantity > 0 ? ($itemPrice / $quantity) : 0);
            $itemPriceForUnitWithoutSale = $isGift ? 0 : ($quantity > 0 ? ($itemPriceWithoutSale / $quantity) : 0);

            OrderItem::create([
                'order_id' => $order->id,
                'item_type' => $cartItem->item_type,
                'item_id' => $item->id,
                'weight' => $item->weight ?? null,
                'weight_type' => $item->weight_type ?? null,
                'weight_for_unit' => $item->weight ?? null, // если нужно хранить за единицу
                'quantity' => $quantity,
                'price_for_unit' => $itemPriceForUnit,
                'price_for_unit_without_sale' => $itemPriceForUnitWithoutSale,
                'unit_sale_percent' => $isGift ? 0 : $cartItem->sale_percent,
                'price' => $itemPrice,
                'is_gift' => $isGift,
            ]);
            $itemsCreated++;
        }

        // Проверяем, что был создан хотя бы один товар заказа
        if ($itemsCreated === 0) {
            // Возвращаем бонусы, если они были списаны
            if ($order->bonus_spent > 0) {
                try {
                    $bonusService->refundBonusesForOrder($order);
                } catch (\Exception $e) {

                }
            }
            $order->delete();
            return redirect()->back()->with('error', 'Не удалось создать заказ: все товары недоступны. Пожалуйста, обновите корзину.');
        }

        // Увеличиваем счетчик использований промокода после успешного создания всех товаров заказа
        // Это гарантирует, что промокод обновится только при успешном создании заказа
        if(!empty($data['promo']) && isset($promocode)) {
            try {
                DB::transaction(function () use ($promocode, $order, $bonusService) {
                    // Блокируем запись для обновления, чтобы предотвратить race condition
                    $promocode = OrderPromocode::lockForUpdate()->find($promocode->id);

                    if (!$promocode) {
                        throw new \Exception('Промокод не найден');
                    }

                    // Проверяем лимит ВНУТРИ транзакции перед увеличением счетчика
                    // Это предотвращает race condition между проверкой и обновлением
                    if($promocode->exceeded_limit !== null && $promocode->number_applications >= $promocode->exceeded_limit) {
                        throw new \Exception('Промокод недействителен или превышен лимит использований.');
                    }

                    $promocode->increment('number_applications');
                    $promocode->refresh();

                    // Проверяем, не превышен ли лимит после увеличения
                    if($promocode->exceeded_limit !== null && $promocode->number_applications >= $promocode->exceeded_limit) {
                        $promocode->status = OrderPromocodeStatusEnum::exceeded_limit;
                    }

                    $promocode->save();
                });
            } catch (\Exception $e) {
                // Если промокод не может быть использован, удаляем заказ и возвращаем бонусы
                if ($order->bonus_spent > 0) {
                    try {
                        $bonusService->refundBonusesForOrder($order);
                    } catch (\Exception $refundException) {
                        \Log::error('Failed to refund bonuses when promocode validation failed', [
                            'order_id' => $order->id,
                            'error' => $refundException->getMessage()
                        ]);
                    }
                }
                $order->delete();
                throw LaravelValidationException::withMessages(['error' => [$e->getMessage()]]);
            }
        }

        // Начисление бонуса за заказ рефералка после успешного создания всех товаров заказа
        // Это гарантирует, что бонусы начислятся только при успешном создании заказа
        $isFirstOrder = !Order::where('user_id', $user->id)
            ->where('id', '!=', $order->id)
            ->exists();

        if ($isFirstOrder) {
            $referralService->rewardReferralBonuses($user);
        }

        // Отправка заказа в Bitrix24 после создания всех товаров
        try {
            // Обновляем заказ из БД и загружаем все необходимые связи
            $order->refresh();
            $order->load([
                'deliveryAddress.city',
                'deliveryAddress.deliveryZone',
                'pickupLocation.city_relation',
                'user',
                'items.item' => function ($query) {
                    // Загружаем бренды для товаров
                    if ($query->getModel() instanceof Product) {
                        $query->with(['brands', 'categories.parent']);
                    }
                }
            ]);
            // UTM не пишем в БД — передаём только в Bitrix

            $utmSent = !empty($utm);
            $utmList = $utmSent ? $utm : [];
            Log::channel('bitrix24')->info('Оформление заказа: UTM метки', [
                'order_id' => $order->id,
                'utm_received' => $utmList,
                'utm_sent_to_bitrix' => $utmSent,
            ]);
            Log::info('Оформление заказа: UTM метки', [
                'order_id' => $order->id,
                'utm_received' => $utmList,
                'utm_sent_to_bitrix' => $utmSent,
            ]);

            OrderBitrixService::push($order, $utm);

            Log::channel('bitrix24')->info('Заказ отправлен в Bitrix24', [
                'order_id' => $order->id,
                'utm_were_sent' => $utmSent,
                'utm_labels' => $utmList,
            ]);
            Log::info('Заказ отправлен в Bitrix24', [
                'order_id' => $order->id,
                'utm_were_sent' => $utmSent,
                'utm_labels' => $utmList,
            ]);
        } catch (\Exception $e) {
            Log::channel('bitrix24')->error('Failed to send order to Bitrix24 from controller', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }

        // Подготовка данных для Google Analytics Ecommerce

        $ecommerceProducts = [];
        $position = 1;

        foreach ($order->items as $orderItem) {
            // Пропускаем подарки (is_gift = true)
            if ($orderItem->is_gift) {
                continue;
            }

            $item = $orderItem->item;

            // Обрабатываем только товары (Product), комбо пропускаем или обрабатываем отдельно
            if (!($item instanceof Product)) {
                continue;
            }

            // Получаем артикул товара
            $productId = $item->article_number ?? (string) $item->id;

            // Получаем название товара
            $productName = $item->name;

            // Цена - фактическая стоимость товара с учетом веса (всегда quantity = 1)
            $productPrice = (float) $orderItem->price;

            // Получаем вес для variant
            $weightForUnit = $orderItem->weight_for_unit ?? $orderItem->weight ?? null;
            $weightType = $orderItem->weight_type ?? $item->weight_type ?? 'кг';
            $variant = $weightForUnit ? "Средний вес: {$weightForUnit}" : null;

            // Получаем бренд (первый бренд из списка или пустая строка)
            $brandName = 'PRIMEBEEF';
            if ($item->brands && $item->brands->isNotEmpty()) {
                $firstBrand = $item->brands->first();
                $brandName = $firstBrand->name ?? 'PRIMEBEEF';
            }

            $ecommerceProducts[] = [
                'id' => (string) $productId,
                'name' => $productName,
                'price' => $productPrice,
                'quantity' => 1, // Всегда 1 согласно требованиям
                'variant' => $variant,
                'brand' => $brandName,
                'categories' => str_replace(' -> ', ' / ', $item->categories->sortBy('id')->first()?->full_breadcrumb ?? '') ?: null,
                'position' => $position++,
            ];
        }

        // Формируем данные для ecommerce события
        $ecommerceData = [
            'ecommerce' => [
                'currencyCode' => 'RUB',
                'purchase' => [
                    'actionField' => [
                        'id' => (string) $order->id,
                    ],
                    'products' => $ecommerceProducts,
                ],
            ],
        ];

        //цена без скидки
        // процент скидки на товар
        //поле вес ЩАС  за единицу товара + добавить weight_for_unit в order_items

        // 4. очистка корзины, если это не 1 клик
        if (empty($data['product_id']) && empty($data['combo_id'])) {
            $cart->items()->delete();
        }
//        dd($data);


        $route = route('user.profile.orders.history.show', $order->id);

        // Убираем UTM из сессии, чтобы редирект на каталог не спровоцировал ещё один редирект (EnsureUtmInUrl)
        // и не было полной перезагрузки страницы — тогда Inertia вернёт 200 с каталогом, onSuccess получит
        // ecommerce_order_data и событие покупки отработает без рефреша.
        session()->forget(CaptureUtmParameters::SESSION_KEY);

        return redirect()->route('catalog.index')->with([
            'success' => <<<HTML
            Заказ №<b>$order->id</b> успешно оформлен и принят в обработку. Следите за статусом в личном кабинете на сайте или по <a href="$route">ссылке</a>
HTML
            ,
            'ecommerce_order_data' => $ecommerceData,
        ]);
    }


    public function create(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['nullable', 'exists:products,id'], //передается при кнопке "Купить сейчас", быстрая покупка. Если передается, то вся корзина не очищается после заказа
            'combo_id' => ['nullable', 'exists:combos,id'], //передается при кнопке "Купить сейчас", быстрая покупка. Если передается, то вся корзина не очищается после заказа
            'promocode' => 'sometimes',
        ]);

        $cart = $this->getCart($request);
        if (!empty($data['product_id']) || !empty($data['combo_id'])) {

            $existingItem = $cart->items()
                ->where('item_type', !empty($data['product_id']) ? Product::class : Combo::class)
                ->where('item_id', $data['product_id'] ?? $data['combo_id'])
                ->where('is_gift', false)
                ->first();

            if (!$existingItem) {
                $cart->items()->create([
                    'item_type' => !empty($data['product_id'])
                        ? Product::class
                        : Combo::class,
                    'item_id'   => $data['product_id'] ?? $data['combo_id'],
                    'quantity'  => 1,
                    'is_gift' => false,
                ]);

                if (!empty($data['product_id'])) {
                    $product = Product::find($data['product_id']);
                    if ($product) {
                        $product->load('giftProducts');
                        $giftProducts = $product->giftProducts()->where('is_active', true)->get();

                        foreach ($giftProducts as $giftProduct) {
                            $cityQuantity = $giftProduct->city_quantity ?? 0;
                            if ($cityQuantity <= 0) {
                                continue;
                            }

                            $existingGiftItem = $cart->items()
                                ->where('item_type', Product::class)
                                ->where('item_id', $giftProduct->id)
                                ->where('is_gift', true)
                                ->first();

                            if ($existingGiftItem) {
                                $existingGiftItem->quantity += 1;
                                $existingGiftItem->save();
                            } else {
                                $cart->items()->create([
                                    'item_type' => Product::class,
                                    'item_id' => $giftProduct->id,
                                    'quantity' => 1,
                                    'is_gift' => true,
                                ]);
                            }
                        }
                    }
                }
            }
        }
        $cartItems = OrderService::getOrderCollect($cart, product_id: $data['product_id'] ?? null, combo_id: $data['combo_id'] ?? null);

        //редирект если в корзине нет товаров
        if($cartItems->count() == 0) return redirect()->route('catalog.index')->with('success', 'Нет товаров для заказа');

        $promocodeStatus = null;
        if(!empty($data['promocode'])) {
            $promocode = OrderPromocode::where('promocode', $data['promocode'])->first();
            if($promocode->checkValidated(false) == true) {
                $promocodeStatus = true;
            } else {
                $promocodeStatus = false;
            }
        }

        $userCityId = session('user_city.city_id');

        $makingOrderBanners = Banner::where('is_active', true)
            ->where('location', BannerLocationEnum::MakingOrderPage)
            ->orderBy('id')
            ->get()
            ->unique('id');

        return Inertia::render('making-order/ui/MakingOrder', [
            'cartItems' => $cartItems,
            'formatedItems' => CartFormattedItemsService::format($cartItems),
            'promocodeStatus' => $promocodeStatus, // true | false | null
            'promocode' => $data['promocode'] ?? null, // true | false | null
            'transactionMethods' =>  TransactionMethodResource::collection(TransactionMethod::where('is_active', true)->get()),
            'pickupLocations' => PickupLocationResource::collection(PickupLocation::when($userCityId, fn($q) => $q->where('city_id', $userCityId))->get()),
            'userAddresses' => Auth::user() ? AddressResource::collection(Auth::user()->addresses) : null,
            'allContactMethods'   => ContactMethod::get(['id', 'name']),
            'banners' => $makingOrderBanners ? BannerResource::collection($makingOrderBanners) : null,
        ]);
    }

    #[Endpoint("Получить расчёт заказа и доставки")]
    #[BodyParam("product_id", "integer", "ID товара. Если указан — заказ рассчитывается по одному товару, корзина не учитывется.", required: false, example: 15)]
    #[BodyParam("combo_id", "integer", "ID Комбо. Если указан — заказ рассчитывается по одному товару, корзина не м.", required: false, example: 16)]
    #[BodyParam("address_id", "integer", "ID Адреса", required: false, example: 3)]
    #[BodyParam("pickup_location_id", "integer", "ID Пункта самовывоза", required: false, example: 3)]
    #[BodyParam("delivery_zone_id", "integer", "ID Зоны доставки. Если параметр не передан, но передан АДРЕС, то бэк возьмет с адреса", required: false, example: 3)]
    #[BodyParam("promo", "string", "Промокод.", required: false, example: "SALE2025")]
    #[BodyParam("delivery_type", "string", "Способ доставки", required: false, example: "courier || pickup")]
    #[BodyParam("delivery_time_from", "string", "Интервал доставки ОТ", required: false)]
    #[BodyParam("delivery_time_to", "string", "Интервал доставки ДО", required: false,)]
    #[BodyParam("delivery_date", "string", "Дата доставки в формате YYYY-MM-DD", required: false)]
    /*    #[Response(["data" => "array"], description: "Массив расчёта")]*/
    #[ResponseFromFile('app/Modules/Order/src/Http/Responses/OrderController.calculateOrder.json')]
    public function calculateOrder(
        OrderCalculateFormRequest $request,
        FirstOrderGiftService $firstOrderGiftService
    ) : JsonResponse
    {
        $data = $request->validated();

        // После редиректа с применённым промокодом фронт может вызвать расчёт без promo в запросе — подставляем из сессии
        if (empty($data['promo']) && $request->session()->has('applied_promocode')) {
            $data['promo'] = $request->session()->get('applied_promocode');
        }

        // use_expiring_bonuses может быть boolean или строкой 'true'/'false'
        $useExpiringBonuses = false;
        if (isset($data['use_expiring_bonuses'])) {
            if (is_bool($data['use_expiring_bonuses'])) {
                $useExpiringBonuses = $data['use_expiring_bonuses'];
            } else {
                $useExpiringBonuses = $data['use_expiring_bonuses'] === 'true' || $data['use_expiring_bonuses'] === true;
            }
        }
        $cart = $this->getCart($request);
        /** @var \User\Models\User|null $authUser */
        $authUser = Auth::user();
        $isQuickBuy = !empty($data['product_id']) || !empty($data['combo_id']);

        if (!$isQuickBuy) {
            $firstOrderGiftService->syncGiftForCart($cart, $authUser, $data['email'] ?? null, $data['phone'] ?? null);
            $cart->refresh();
        }

        $cartItems = OrderService::getOrderCollect($cart, product_id: $data['product_id'] ?? null, combo_id: $data['combo_id'] ?? null);
        $cartItems = $firstOrderGiftService->appendGiftToCollection($cartItems, $authUser, $data['email'] ?? null, $data['phone'] ?? null);

        $availableBonus = null;
        $expiringBonusAmount = null;
        if ($authUser instanceof \User\Models\User) {
            /** @var \User\Models\User $authUser */
            $bonusService = app(BonusService::class);
            $availableBonus = $bonusService->getAvailableBalance($authUser);

            if ($useExpiringBonuses) {
                $now = \Carbon\Carbon::now();
                $closestExpiryDate = $authUser->bonusHistories()
                    ->where('type', \App\Modules\Bonus\Bonus\src\Enums\BonusTypeEnum::Accrual)
                    ->where('status', \App\Modules\Bonus\Bonus\src\Enums\BonusStatusEnum::Active)
                    ->where('remaining_amount', '>', 0)
                    ->where('active_date', '<=', $now)
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '>', $now)
                    ->orderBy('expires_at', 'asc')
                    ->value('expires_at');

                if ($closestExpiryDate) {
                    $expiringBonusAmount = $authUser->bonusHistories()
                        ->where('type', \App\Modules\Bonus\Bonus\src\Enums\BonusTypeEnum::Accrual)
                        ->where('status', \App\Modules\Bonus\Bonus\src\Enums\BonusStatusEnum::Active)
                        ->where('remaining_amount', '>', 0)
                        ->where('expires_at', $closestExpiryDate)
                        ->sum('remaining_amount');
                }
            }
        }

        $promocode = null;
        if(!empty($data['promo'])) {
            $promocode = OrderPromocode::where('promocode', $data['promo'])->first();

            // Проверяем валидность промокода БЕЗ увеличения счетчика
            if(!$promocode || $promocode->checkValidated(false) != true) {
                throw LaravelValidationException::withMessages(['error' => ['Промокод недействителен или превышен лимит использований.']]);
            }

            // Дополнительная проверка лимита
            if($promocode->exceeded_limit !== null && $promocode->number_applications >= $promocode->exceeded_limit) {
                throw LaravelValidationException::withMessages(['error' => ['Промокод недействителен или превышен лимит использований.']]);
            }
        }

        $address = Address::find($data['address_id'] ?? null);
        if (isset($data['address_id'])) {
            $data['delivery_address_id'] = $data['address_id'];
        }

        $pickupLocation = null;
        $deliveryDate = $this->normalizeDeliveryDate($data['delivery_date'] ?? null);
        $cityId = $data['city_id'] ?? null;

        // Фолбэк для delivery_type: фронт может не прислать его (например, при смене зоны доставки).
        // Если указан pickup_location_id — это самовывоз, если указан delivery_zone_id или address_id — это курьер.
        $deliveryType = $data['delivery_type'] ?? null;
        if ($deliveryType === null) {
            if (!empty($data['pickup_location_id'])) {
                $deliveryType = 'pickup';
            } elseif (!empty($data['delivery_zone_id']) || !empty($data['address_id'])) {
                $deliveryType = 'courier';
            }
        }

        if (!empty($data['pickup_location_id'])) {
            $pickupLocation = PickupLocation::find($data['pickup_location_id']);
            if ($pickupLocation) {
                $deliveryDate = $deliveryDate ?? Carbon::now()->addDays($pickupLocation->delivery_time)->format('Y-m-d');
            }
        } else {
            if (!empty($data['city_id'])) {
                $cityId = $data['city_id'];
            }
            elseif (!empty($data['address_id']) && $address) {
                $cityId = $address->city_id;
            }

            if ($cityId) {
                $pickupLocation = PickupLocation::where('city_id', $cityId)
                ->orderBy('delivery_time', 'asc')
                ->first();

                if ($pickupLocation) {
                    $deliveryDate = $deliveryDate ?? Carbon::now()->addDays($pickupLocation->delivery_time)->format('Y-m-d');
                }
            }
        }



        $orderCalculate = OrderService::calculateOrder(
            collectCartItems: $cartItems,
            promocode: $promocode ?? null,
            deliveryZone: DeliveryZone::find($data['delivery_zone_id'] ?? null),
            address: $address,
            pickupLocation: $pickupLocation,
            delivery_date: $deliveryDate,
            delivery_time_from: $data['delivery_time_from'] ?? null,
            delivery_time_to: $data['delivery_time_to'] ?? null,
            deliveryType: $deliveryType,
            cityIdFromRequest: $cityId ?? null,
            useExpiringBonuses: $useExpiringBonuses,
            expiringBonusAmount: $expiringBonusAmount,
            availableBonus: $availableBonus
        );

        return response()->json([
            'data' => $orderCalculate,
        ], 200);
    }

    #[Endpoint("Проверить промокод", "")]
    #[BodyParam("promocode", "string", "", required: true, example: "TEST_PROMO")]
    public function promo_check(Request $request)
    {
        $data = $request->validate(
            [
                'promocode' => 'required'
            ]);
        $promocode = OrderPromocode::where('promocode', $data['promocode'])->first();
        /*            if(!$promocode || $promocode->checkValidated() != true) {
                        throw LaravelValidationException::withMessages(['error' => ['Промокод недействителен или превышен лимит использований.']]);
                    }*/
        if ($promocode?->checkValidated(false) == true) {
            $request->session()->put('applied_promocode', $data['promocode']);
            return redirect()->back()->with([
                'success' => 'Промокод применён',
                'applied_promocode' => $data['promocode'],
            ]);
        }

        return redirect()->back()->withErrors([
            'promocode' => 'Промокод недействителен'
        ]);
    }

    #[Endpoint("Пункты самовывоза по городу", "")]
    #[BodyParam("city_id", type: "integer", required: true, example: "232")]
    #[ResponseFromApiResource(PickupLocationResource::class, PickupLocation::class, collection: true)]
    public function getPickupLocationByCity(Request $request) : JsonResponse | RedirectResponse
    {
        $data = $request->validate(
            [
                'city_id' => 'required|exists:cities,id'
            ]);
        $pickupLocations = PickupLocation::where('city_id', $data['city_id'])->get();
        return response()->json(['data' => [
            'pickupLocations' => PickupLocationResource::collection($pickupLocations),
        ]], 200);
    }

    /**
     * Нормализует дату доставки: если пришла ISO-строка с временем (например от JS Date),
     * интерпретируем в таймзоне приложения и возвращаем только календарную дату Y-m-d,
     * чтобы не было сдвига на день из-за UTC.
     */
    protected function normalizeDeliveryDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_object($value) && method_exists($value, 'format')) {
            return $value->format('Y-m-d');
        }
        $str = (string) $value;
        if (str_contains($str, 'T')) {
            return Carbon::parse($str)->timezone(config('app.timezone'))->format('Y-m-d');
        }
        return $str;
    }
}
