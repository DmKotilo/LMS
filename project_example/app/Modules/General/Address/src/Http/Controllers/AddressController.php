<?php

namespace Address\Http\Controllers;
use Address\Http\Resources\AddressResource;
use Address\Model\Address;
use App\Http\Controllers\Controller;
use App\Modules\Banner\src\Http\Enums\BannerTypeEnum;
use App\Modules\Banner\src\Http\Resources\BannerResource;
use App\Modules\Blog\Recipe\src\Http\Resources\RecipeCardResource;
use App\Modules\Bonus\Bonus\src\Http\Resources\BonusCardResource;
use App\Modules\Combo\src\Http\Resources\ComboCardResource;
use App\Modules\Delivery\src\Http\Resources\PickupLocationResource;
use App\Modules\Order\src\Http\Resources\OrderItemResource;
use App\Modules\Product\Product\src\Enums\PageSlugEnum;
use App\Modules\Product\Product\src\Models\RecomendedProductPage;
use App\Modules\Review\src\Enums\ReviewStatusEnum;
use App\Modules\Review\src\Http\Resources\ReviewHomeCardResource;
use Banner\Models\Banner;
use Catalog\Http\Resources\ProductCardResource;
use Category\Http\Resources\MainCategoryResource;
use Category\Models\MainCategory;
use Combo\Models\Combo;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Product\Models\Product;
use Recipe\Models\Recipe;
use Review\Models\ProductReview;
use Review\Models\Review;

#[Group('Заказы')]
class AddressController extends Controller
{
    #[Endpoint('Добавление нового адреса', 'Сохраняет один адрес для текущего пользователя.')]
    #[BodyParam("address", "array", "Адрес", required: false)]
    #[BodyParam("address.postal_code", "string", "Почтовый индекс", required: false,example: "344001")]
    #[BodyParam("address.region", "string", "Регион", required: false,example: "Ростовская область")]
    #[BodyParam("address.city_dadata", "string", "Город(нас.пункт)", required: false,example: "Ростов-на-Дону")]
    #[BodyParam("address.city_id", "integer", "ID города", required: false,example: 1)]
    #[BodyParam("address.street", "string", "Улица", required: false,example: "Пушкина")]
    #[BodyParam("address.house", "string", "Дом", required: false,example: "12")]
    #[BodyParam("address.entrance", "string", "Подъезд", required: false,example: "8")]
    #[BodyParam("address.floor", "string", "Этаж", required: false,example: "5")]
    #[BodyParam("address.apartment", "string", "Квартира", required: false,example: "25")]
    #[BodyParam("address.value_dadata", "string", "Город улица номер дома", required: true)]
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'address' => 'required|array',
            'address.city_id' => 'nullable|exists:cities,id',
            'address.delivery_zone_id' => 'nullable|exists:delivery_zones,id',
            'address.postal_code' => 'nullable|string',
            'address.city_dadata' => 'nullable|string',
            'address.region' => 'nullable|string',
            'address.entrance' => 'nullable|string',
            'address.floor' => 'nullable|string',
            'address.apartment' => 'nullable|string',
            'address.value_dadata' => 'required|string',
            'address.dadata_json' => 'nullable|array',
        ]);

        $addr = $request->input('address');

        $city = null;
        $street = null;
        $house  = null;

        if (!empty($addr['value_dadata'])) {
            // ✅ Правильная регулярка: ищем "ул. ..." и "дом/д ..."
            if (preg_match('/^(.*?),\s*ул\.\s*([^,]+),\s*(\d+).*/ui', $addr['value_dadata'], $matches)) {
                $city = trim($matches[1]);
                $street = trim($matches[2]);
                $house  = trim($matches[3]);
            } else {
                $city = null;
                $street = $addr['value_dadata'];
                $house  = null;
            }
        }

        // ✅ Перезаписываем $addr['street'] и $addr['house'] тем, что распарсили
        $addr['city_dadata'] = $addr['city_dadata'] ?? $city;
        $addr['street'] = $street;
        $addr['house']  = $house;

        $finalAddress = Address::formatFinalAddress($addr);

        $isPrimary = false;

        if ($user) {
            $hasActiveAddresses = $user->addresses()
                ->where('user_deleted', false)
                ->exists();

            $hasPrimaryAddress = $user->addresses()
                ->where('user_deleted', false)
                ->where('is_primary', true)
                ->exists();

            if (!$hasActiveAddresses || !$hasPrimaryAddress) {
                $isPrimary = true;
            }
        }

        $address = Address::create([
            'dadata_json'     => $addr['dadata_json'] ?? null,
            'postal_code'     => $addr['postal_code'] ?? null,
            'city_id'         => $addr['city_id'] ?? null,
            'city_dadata'     => $addr['city_dadata'] ?? null,
            'region'          => $addr['region'] ?? null,
            'delivery_zone_id'=> $addr['delivery_zone_id'] ?? null,
            'entrance'        => $addr['entrance'] ?? null,
            'floor'           => $addr['floor'] ?? null,
            'apartment'       => $addr['apartment'] ?? null,
            'car_pass'        => false,
            'is_primary'      => $isPrimary,
            'value_dadata'    => $addr['value_dadata'] ?? null,
            'street'          => $street,
            'house'           => $house,
            'final_address'   => $finalAddress,
        ]);

        if($user) {
            $user->addresses()->save($address);
        }

        return redirect()->back()->with([
            'success' => 'Адрес успешно сохранён',
            'address_id' => $address->id,
        ]);
    }

    #[Endpoint('Добавление нового адреса API', 'Сохраняет один адрес')]
    #[BodyParam("save", "boolean", "Сохранить в лк (если авторизован)", required: false)]
    #[BodyParam("address", "array", "Адрес", required: false)]
    #[BodyParam("address.postal_code", "string", "Почтовый индекс", required: false,example: "344001")]
    #[BodyParam("address.region", "string", "Регион", required: false,example: "Ростовская область")]
    #[BodyParam("address.city_dadata", "string", "Город(нас.пункт)", required: false,example: "Ростов-на-Дону")]
    #[BodyParam("address.city_id", "integer", "ID города", required: false,example: 1)]
    #[BodyParam("address.street", "string", "Улица", required: false,example: "Пушкина")]
    #[BodyParam("address.house", "string", "Дом", required: false,example: "12")]
    #[BodyParam("address.entrance", "string", "Подъезд", required: false,example: "8")]
    #[BodyParam("address.floor", "string", "Этаж", required: false,example: "5")]
    #[BodyParam("address.apartment", "string", "Квартира", required: false,example: "25")]
    #[BodyParam("address.value_dadata", "string", "Город улица номер дома", required: true)]
    public function storeApi(Request $request) : JsonResponse
    {
        $user = Auth::user();

        $request->validate([
            'address' => 'required|array',
            'address.city_id' => 'nullable|exists:cities,id',
            'address.delivery_zone_id' => 'nullable|exists:delivery_zones,id',
            'address.postal_code' => 'nullable|string',
            'address.city_dadata' => 'nullable|string',
            'address.region' => 'nullable|string',
            'address.entrance' => 'nullable|string',
            'address.floor' => 'nullable|string',
            'address.apartment' => 'nullable|string',
            'address.value_dadata' => 'required|string',
            'address.dadata_json' => 'nullable|array',
            'save' => 'nullable|boolean',
        ]);

        $addr = $request->input('address');

        $city = null;
        $street = null;
        $house  = null;

        if (!empty($addr['value_dadata'])) {
            // ✅ Правильная регулярка: ищем "ул. ..." и "дом/д ..."
            if (preg_match('/^(.*?),\s*ул\.\s*([^,]+),\s*(\d+).*/ui', $addr['value_dadata'], $matches)) {
                $city = trim($matches[1]);
                $street = trim($matches[2]);
                $house  = trim($matches[3]);
            } else {
                $city = null;
                $street = $addr['value_dadata'];
                $house  = null;
            }
        }

        // ✅ Перезаписываем $addr['street'] и $addr['house'] тем, что распарсили
        $addr['city_dadata'] = $addr['city_dadata'] ?? $city;
        $addr['street'] = $street;
        $addr['house']  = $house;

        $finalAddress = Address::formatFinalAddress($addr);

        $isPrimary = false;

        if ($user && $request->input('save') == true) {
            $hasActiveAddresses = Address::where('user_id', $user->id)
                ->where('user_deleted', false)
                ->exists();

            $hasPrimaryAddress = Address::where('user_id', $user->id)
                ->where('user_deleted', false)
                ->where('is_primary', true)
                ->exists();

            if (!$hasActiveAddresses || !$hasPrimaryAddress) {
                $isPrimary = true;
            }
        }

        $address = Address::create([
            'dadata_json'     => $addr['dadata_json'] ?? null,
            'user_id'         => ($user && $request->input('save') == true) ? $user->id : null,
            'postal_code'     => $addr['postal_code'] ?? null,
            'city_id'         => $addr['city_id'] ?? null,
            'city_dadata'     => $addr['city_dadata'] ?? null,
            'region'          => $addr['region'] ?? null,
            'delivery_zone_id'=> $addr['delivery_zone_id'] ?? null,
            'entrance'        => $addr['entrance'] ?? null,
            'floor'           => $addr['floor'] ?? null,
            'apartment'       => $addr['apartment'] ?? null,
            'car_pass'        => false,
            'is_primary'      => $isPrimary,
            'value_dadata'    => $addr['value_dadata'] ?? null,
            'street'          => $street,
            'house'           => $house,
            'final_address'   => $finalAddress,
        ]);

        return response()->json(['data' => [
            'address' => AddressResource::make($address),
        ]], 200);
    }

}
