<?php

namespace App\Modules\Cart\src\Http\Controllers;

use Address\Http\Resources\AddressResource;
use App\Http\Controllers\Controller;
use App\Modules\Banner\src\Http\Enums\BannerLocationEnum;
use App\Modules\Banner\src\Http\Resources\BannerResource;
use App\Modules\Blog\Recipe\src\Http\Resources\RecipeCardResource;
use App\Modules\Cart\src\Enums\CartItemTypeEnum;
use App\Modules\Cart\src\Enums\CartStatusEnum;
use App\Modules\Cart\src\Http\Resources\CartResource;
use App\Modules\Cart\src\Http\Resources\ProductCartResource;
use App\Modules\Cart\src\Traits\GetCart;
use App\Modules\Cart\src\Services\FirstOrderGiftService;
use App\Modules\Delivery\src\Http\Resources\PickupLocationResource;
use Banner\Models\Banner;
use Cart\Models\UserCart;
use Catalog\Http\Resources\ProductCardResource;
use Combo\Models\Combo;
use Delivery\Models\PickupLocation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseField;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\UrlParam;
use Order\Models\Order;
use Product\Models\AutoRelatedProduct;
use Product\Models\Product;
use Product\Models\RelatedProduct;
use Recipe\Models\Recipe;
use User\Models\User;

#[Group('Корзина')]
class CartController extends Controller
{
    use GetCart;

    public function __construct(
        protected FirstOrderGiftService $firstOrderGiftService
    ) {
    }

    public function index(Request $request)
    {
//        $cart = $this->getCart($request)->load(['items.item' => fn($q) => $q->active()]);
        $cart = $this->getCart($request);
        $this->firstOrderGiftService->syncGiftForCart($cart, $this->resolveUser(Auth::user()));
        $cart->load(['items.item' => function ($q) {
            $q->when($q->getModel() instanceof Product, fn($query) => $query->active());
        }]);

        // ❌ УДАЛЕНО: Явная загрузка products не нужна
        // getTotalPriceAttribute() сам загрузит products при необходимости
        $cartProductIds = $cart->items
            ->filter(fn($item) => $item->item !== null)
            ->pluck('item_id')
            ->toArray();

        //специальные предложения todo
        $special_products = Product::active()->whereNotIn('id', $cartProductIds)
            ->inRandomOrder()
            ->limit(7)
            ->get();

        // связанные товары к продуктам из корзины
        $relatedProducts = RelatedProduct::whereIn('product_id', $cartProductIds)
            ->with(['related' => fn($q) => $q->active()])
            ->get()
            ->pluck('related')
            ->filter(fn($item) => $item !== null)
            ->unique('id')
            ->whereNotIn('id', $cartProductIds);

        if ($relatedProducts->isEmpty()) {
            $relatedProducts = AutoRelatedProduct::whereIn('product_id', $cartProductIds)
                ->with(['related' => fn($q) => $q->active()])
                ->orderByDesc('confidence')
                ->get()
                ->pluck('related')
                ->filter(fn($item) => $item !== null)
                ->unique('id')
                ->whereNotIn('id', $cartProductIds);
        }

        $recipes = Recipe::whereHas('products', function ($q) use ($cartProductIds) {
            $q->whereIn('products.id', $cartProductIds);
        })
            ->inRandomOrder()
            ->get();

        $userCityId = session('user_city.city_id');


        $isNewClient = false;
        $user = Auth::user();

        if (!$user) {
            $isNewClient = true;
        } else {
            $hasOrders = Order::where('user_id', $user->id)
                ->orWhere('email', $user->email)
                ->orWhere('phone', $user->phone)
                ->exists();

            if (!$hasOrders) {
                $isNewClient = true;
            }
        }

        $cartBanners = Banner::where('is_active', true)
            ->where('location', BannerLocationEnum::CartPage)
            ->where('only_for_new_client', $isNewClient)
            ->orderBy('id')
            ->get()
            ->unique('id');

        return Inertia::render('cart/ui/CartPage', [
            'cart' => new CartResource($cart),
            'special_products' => ProductCardResource::collection($special_products),
            'recipes' => RecipeCardResource::collection($recipes),
            'related_products' => ProductCartResource::collection($relatedProducts),
            'pickupLocations' => PickupLocationResource::collection(PickupLocation::when($userCityId, fn($q) => $q->where('city_id', $userCityId))->get()),
            'userAddresses' => Auth::user() ? AddressResource::collection(Auth::user()->addresses) : null,
            'cartBanners' => $cartBanners ? BannerResource::collection($cartBanners) : null,
        ]);
    }



    #[Endpoint('Добавить товар или комбо-набор в корзину', 'Добавляет выбранный товар или комбо-набор в корзину пользователя')]
    #[BodyParam('type', 'string', 'Тип элемента: product или combo', required: true, example: 'product')]
    #[BodyParam('id', 'integer', 'ID товара или комбо-набора', required: true, example: 12)]
    #[BodyParam('quantity', 'integer', 'Количество (по умолчанию 1)', required: false, example: 3)]
    #[ResponseFromApiResource(CartResource::class, UserCart::class)]
    public function add(Request $request)
    {
        $request->validate([
            'type' => ['required', 'string', Rule::in(['product', 'combo'])],
            'id' => ['required', 'integer'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $quantity = $request->input('quantity', 1);

        $itemType = match ($request->type) {
            'product' => CartItemTypeEnum::Product->value,
            'combo' => CartItemTypeEnum::Combo->value,
        };

        if (!class_exists($itemType) || !($item = $itemType::find($request->id))) {
            return back()->withErrors(['id' => 'Товар не найден']);
        }

        $cart = $this->getCart($request);

        $query = $cart->items()
            ->where('item_type', $itemType)
            ->where('item_id', $item->id);

        $cartItem = (clone $query)->where('is_gift', false)->first();

        if (!$cartItem) {
            $cartItem = (clone $query)->where('is_gift', true)->first();
        }

        $wasNewItem = false;
        if ($cartItem) {
            if ($cartItem->is_gift) {
                return redirect()->back()->with([
                    'error' => 'Подарочный товар нельзя добавить вручную',
                ]);
            }
            $cartItem->quantity += $quantity;
            $cartItem->save();
        } else {

            $cart->items()->create([
                'item_type' => $itemType,
                'item_id' => $item->id,
                'quantity' => $quantity,
                'is_gift' => false, // Явно указываем, что это не подарок
            ]);
            $wasNewItem = true;
        }

        if ($item instanceof Product) {
            $item->load('giftProducts');
            $giftProducts = $item->giftProducts()->where('is_active', true)->get();

            foreach ($giftProducts as $giftProduct) {
                $cityQuantity = $giftProduct->city_quantity ?? 0;
                if ($cityQuantity <= 0) {
                    continue; // Подарок недоступен - пропускаем
                }

                // Добавляем подарки: 1 подарок на 1 единицу товара
                $giftQuantity = $quantity;

                $existingGiftItem = $cart->items()
                    ->where('item_type', CartItemTypeEnum::Product->value)
                    ->where('item_id', $giftProduct->id)
                    ->where('is_gift', true)
                    ->first();

                if ($existingGiftItem) {
                    $existingGiftItem->quantity = $existingGiftItem->quantity + 1;
                    $existingGiftItem->save();
                } else {
                    // Создаём новый подарок
                    $cart->items()->create([
                        'item_type' => CartItemTypeEnum::Product->value,
                        'item_id' => $giftProduct->id,
                        'quantity' => 1,
                        'is_gift' => true,
                    ]);
                }
            }
        }

        $this->firstOrderGiftService->syncGiftForCart($cart, $this->resolveUser(Auth::user()));

        return redirect()->back()->with([
            'success' => 'Товар добавлен в корзину'
        ]);
    }

    #[Endpoint('Повторить заказ', 'Добавляет все товары из выбранного заказа в корзину пользователя. (api)')]
    #[Response(['success' => 'Товары из заказа добавлены в корзину'], description: 'Если товары успешно добавлены')]
    public function retryOrder(Request $request, Order $order)
    {
        $cart = $this->getCart($request);

        if ($order->product_id) {
            $item = \Product\Models\Product::find($order->product_id);
            if ($item) {
                $cartItem = $cart->items()
                    ->where('item_type', \Product\Models\Product::class)
                    ->where('item_id', $item->id)
                    ->first();

                if ($cartItem) {
                    $cartItem->quantity += 1;
                    $cartItem->save();
                } else {
                    $cart->items()->create([
                        'item_type' => \Product\Models\Product::class,
                        'item_id'   => $item->id,
                        'quantity'  => 1,
                    ]);
                }
            }
        }elseif ($order->combo_id){
            $item = Combo::with('products')->find($order->combo_id);
            if ($item) {
                $cartItem = $cart->items()
                    ->where('item_type', Combo::class)
                    ->where('item_id', $item->id)
                    ->first();

                if ($cartItem) {
                    $cartItem->quantity += 1;
                    $cartItem->save();
                } else {
                    $cart->items()->create([
                        'item_type' => Combo::class,
                        'item_id'   => $item->id,
                        'quantity'  => 1,
                    ]);
                }
            }
        } else {
            foreach ($order->items as $orderItem) {
                $item = $orderItem->item;
                if (!$item) continue;

                $itemType = get_class($item);

                $cartItem = $cart->items()
                    ->where('item_type', $itemType)
                    ->where('item_id', $item->id)
                    ->first();

                if ($cartItem) {
                    $cartItem->quantity += $orderItem->quantity;
                    $cartItem->save();
                } else {
                    $cart->items()->create([
                        'item_type' => $itemType,
                        'item_id'   => $item->id,
                        'quantity'  => $orderItem->quantity,
                    ]);
                }
            }
        }

        $this->firstOrderGiftService->syncGiftForCart($cart, $this->resolveUser(Auth::user()));

        return redirect()->route('order.create');
    }




    #[Endpoint('Удалить товар из корзины', 'Удаляет или уменьшает количество товара или комбо-набора в корзине по item_id и item_type')]
    #[BodyParam('item_id', 'integer', 'ID товара или комбо-набора', required: true, example: 12)]
    #[BodyParam('item_type', 'string', 'Тип элемента: "product" или "combo"', required: true, example: 'product')]
    #[BodyParam('quantity', 'integer', 'Количество для уменьшения (по умолчанию 1). Если количество равно или превышает текущее, товар будет удалён.', required: false, example: 1)]
    #[Response(description: 'Товар удалён из корзины')]

    public function remove(Request $request)
    {
        $request->validate([
            'item_id' => ['required', 'integer'],
            'item_type' => ['required', 'string'], // 'product' или 'combo'
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $cart = $this->getCart($request);
        $quantity = $request->input('quantity', 1);

        $itemTypeEnum = $request->item_type === 'combo'
            ? CartItemTypeEnum::Combo
            : CartItemTypeEnum::Product;

        // Ищем товар для удаления
        // Если в корзине есть 2 товара с одинаковым item_id (один обычный, один подарок),
        // приоритет отдаём обычному товару (is_gift = false), чтобы удалить тот, у которого цена > 0
        $query = $cart->items()
            ->where('item_id', $request->item_id)
            ->where('item_type', $itemTypeEnum);

        // Сначала пытаемся найти обычный товар (не подарок)
        $cartItem = (clone $query)->where('is_gift', false)->first();

        // Если не найден, ищем подарок
        if (!$cartItem) {
            $cartItem = (clone $query)->where('is_gift', true)->first();
        }

        if (!$cartItem) {
            return redirect()->back()->with('error', 'Товар не найден в корзине');
        }

        $item = $cartItem->item;

        // Проверяем, можно ли удалять подарок за первый заказ
        // Согласно ТЗ: подарок за первый заказ нельзя удалить, ИСКЛЮЧЕНИЕ - если в корзине 2 товара с одинаковым item_id
        if ($item instanceof Product && ($item->is_first_order_gift ?? false)) {
            // Считаем количество товаров с таким же item_id в корзине
            // Оба товара должны иметь is_first_order_gift = true (один добавлен вручную, другой автоматически)
            $duplicatesCount = $cart->items()
                ->where('item_type', $itemTypeEnum)
                ->where('item_id', $request->item_id)
                ->count();

            // Если это подарок за первый заказ (is_gift = true)
            if ($cartItem->is_gift) {
                // Если дубликатов <= 1, запрещаем удаление (обычный случай)
                if ($duplicatesCount <= 1) {
                    return redirect()->back()->with('error', 'Подарочный товар нельзя удалить из корзины');
                }
                // Если дубликатов > 1 (например, пользователь добавил товар вручную + автоматический подарок),
                // разрешаем удаление одного из них
                // После удаления syncGiftForCart проверит сумму и удалит второй, если сумма < 10000
            }
            // Если это обычный товар (is_gift = false) с is_first_order_gift = true,
            // его можно удалить всегда (даже если есть подарок-дубликат)
            // После удаления syncGiftForCart проверит сумму и удалит подарок, если нужно
        } elseif ($cartItem->is_gift) {
            // Обычные подарки при покупке товара (из giftProducts, не за первый заказ) можно удалять
            // Согласно ТЗ: можно удалить с подтверждением (подтверждение на фронте)
            // Продолжаем выполнение
        }

        // Вычисляем новое количество основного товара
        $newQuantity = max(0, $cartItem->quantity - $quantity);

        // Если удаляем основной товар (не подарок), нужно обработать подарки из giftProducts
        // Согласно ТЗ: если основной товар удаляется → подарок автоматически удаляется
        // Если количество уменьшается → количество подарков пересчитывается (1 подарок на 1 единицу)
        if (!$cartItem->is_gift && $item instanceof Product) {
            $item->loadMissing('giftProducts');
            $giftProducts = $item->giftProducts()->where('is_active', true)->get();

            if ($giftProducts->isNotEmpty()) {
                foreach ($giftProducts as $giftProduct) {
                    $giftCartItem = $cart->items()
                        ->where('item_type', CartItemTypeEnum::Product->value)
                        ->where('item_id', $giftProduct->id)
                        ->where('is_gift', true)
                        ->first();

                    if ($giftCartItem) {
                        if ($newQuantity <= 0) {
                            // Если основной товар удаляется полностью, удаляем подарок
                            $giftCartItem->delete();
                        } else {
                            // Если количество уменьшается, пересчитываем количество подарков
                            // 1 подарок на 1 единицу основного товара
                            $giftCartItem->quantity = $newQuantity;
                            $giftCartItem->save();
                        }
                    }
                }
            }
        }

        // Обновляем или удаляем основной товар
        if ($cartItem->quantity > $quantity) {
            $cartItem->quantity = $newQuantity;
            $cartItem->save();
        } else {
            $cartItem->delete();
        }
        // Синхронизируем подарок за первый заказ (проверит сумму и удалит, если нужно)
        $this->firstOrderGiftService->syncGiftForCart($cart, $this->resolveUser(Auth::user()));

        return redirect()->back()->with('success', 'Товар удалён из корзины');
    }

    #[Endpoint('Очистка корзины')]
    #[Response( description: 'Корзина успешно очищена')]
    public function clear(Request $request)
    {
        $cart = $this->getCart($request);

        $cart->items()->delete();

        $this->firstOrderGiftService->syncGiftForCart($cart, $this->resolveUser(Auth::user()));

        return redirect()->back()->with([
            'success' => 'Корзина успешно очищена',
        ]);
    }

    public function addRecipeToCart(Request $request, int $recipeId)
    {
        $recipe = Recipe::with('products')->find($recipeId);

        if (! $recipe) {
            return redirect()->back()->with(['error' => 'рецепт не найден']);
        }


        $cart = $this->getCart($request);

        $products = $recipe->products()
            ->where('is_active', true)
            ->whereNotNull('price')
            ->whereHas('categories')
            ->get()
            ->filter(fn($product) => $product->city_quantity > 0);


        if ($products->isEmpty()) {
            return redirect()->back()->with(['error' => 'нет доступных товаров']);
        }

        foreach ($products as $product) {
            $exists = $cart->items()
                ->where('item_type', Product::class)
                ->where('item_id', $product->id)
                ->exists();

            if (!$exists) {
                $cart->items()->create([
                    'item_type' => Product::class,
                    'item_id' => $product->id,
                    'quantity' => 1,
                ]);
            }
        }

        $this->firstOrderGiftService->syncGiftForCart($cart, $this->resolveUser(Auth::user()));

        return redirect()->back()->with([
            'success' => 'Товары успешно добавлены в корзину!'
        ]);
    }

    #[Endpoint('Проверить и синхронизировать подарок за первый заказ', 'Проверяет наличие подарка в корзине на основе email и телефона клиента')]
    #[BodyParam('email', 'string', 'Email клиента', required: false, example: 'user@example.com')]
    #[BodyParam('phone', 'string', 'Телефон клиента', required: false, example: '+79991234567')]
    #[Response(description: 'Редирект назад с сообщением об успехе')]
    public function checkGift(Request $request)
    {
        $data = $request->validate([
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
        ]);

        $cart = $this->getCart($request);
        $user = $this->resolveUser(Auth::user());

        $this->firstOrderGiftService->syncGiftForCart(
            $cart,
            $user,
            $data['email'] ?? null,
            $data['phone'] ?? null
        );

        return redirect()->back()->with('success', 'Корзина обновлена');
    }

    protected function resolveUser(?\Illuminate\Contracts\Auth\Authenticatable $authUser): ?User
    {
        return $authUser instanceof User ? $authUser : null;
    }
}
