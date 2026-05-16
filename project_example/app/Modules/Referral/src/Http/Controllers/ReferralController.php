<?php

namespace App\Modules\Referral\src\Http\Controllers;

use Address\Http\Resources\AddressResource;
use App\Http\Controllers\Controller;
use App\Modules\Blog\Recipe\src\Http\Resources\RecipeCardResource;
use App\Modules\Cart\src\Enums\CartItemTypeEnum;
use App\Modules\Cart\src\Enums\CartStatusEnum;
use App\Modules\Cart\src\Http\Resources\CartResource;
use App\Modules\Cart\src\Http\Resources\ProductCartResource;
use App\Modules\Cart\src\Traits\GetCart;
use App\Modules\Delivery\src\Http\Resources\PickupLocationResource;
use Cart\Models\UserCart;
use Catalog\Http\Resources\ProductCardResource;
use Combo\Models\Combo;
use Delivery\Models\PickupLocation;
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
use Product\Models\Product;
use Product\Models\RelatedProduct;
use Recipe\Models\Recipe;
use Referral\Models\Referral;
use Str;
use User\Models\User;


class ReferralController extends Controller
{
    // получить ссылку для приглашения
    public function getReferralLink(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->to(
                url()->previous() . (str_contains(url()->previous(), '?') ? '&' : '?') . 'modal=login'
            );
        }
        $user = auth()->user();
        // ищем или создаём код для пользователя
        $referral = Referral::firstOrCreate(
            ['inviter_id' => $user->id, 'invited_id' => null],
            ['referral_code' => Str::uuid()]
        );
        return redirect()->back()->with([
            'message' => url('?modal=login&ref=' . $referral->referral_code),
        ]);
    }


}
