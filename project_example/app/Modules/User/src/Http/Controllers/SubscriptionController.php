<?php

namespace User\Http\Controllers;

use Address\Http\Resources\AddressResource;
use Address\Model\Address;
use Address\Model\ContactMethod;
use Alert\Models\Alert;
use App\Http\Controllers\Controller;
use App\Modules\Alert\src\Http\Resources\AlertResource;
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
use App\Modules\Order\src\Http\Resources\ProductOrderResource;
use App\Modules\Review\src\Enums\ReviewStatusEnum;
use App\Modules\Review\src\Http\Resources\ReviewResource;
use App\Modules\User\src\Enums\DeleteReasonEnum;
use App\Modules\User\src\Models\DeletedUser;
use App\Modules\UserSetting\src\Http\Resources\UserSettingResource;
use App\MoonShine\Resources\Delivery\DeliveryZoneResource;
use App\Services\Bitrix24\BitrixSubscriptionService;
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
use User\Enums\GenderEnum;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Product\Models\Product;
use User\Models\Subscription;
use User\Models\User;
use UserSetting\Models\UserSetting;

#[Group('Подписка о вкусном и полезном')]
class SubscriptionController extends Controller
{
    #[Endpoint('Подписка на рассылку', 'Создать новую подписку на рассылку')]
    #[BodyParam('email', description: 'Email пользователя для подписки', required: true, example: 'user@example.com')]
    #[BodyParam('name', description: 'Имя пользователя для подписки', required: false, example: 'Иванов Иван')]
    #[Response(status: 200, description: "Вы подписались на рассылку!", content: '{"success": "Вы подписались на рассылку!"}')]
    #[Response(status: 422, description: "Этот email уже подписан.", content: '{"error": "Этот email уже подписан."}')]
    public function store(Request $request, BitrixSubscriptionService $bitrixSubscriptionService)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'name' => 'nullable|max:255',
        ]);

        $email = $validated['email'];
        $existing = Subscription::where('email', $email)->first();

        if ($existing) {
            return back()->with(['error' => 'Этот email уже подписан.']);
        }
        $data = [
            'is_active' => true,
            'name' => $validated['name'] ?? null
        ];

        /** @var User|null $user */
        $user = Auth::user();

        if ($user) {
            $data['user_id'] = $user->id;
        }

        $subscription = Subscription::updateOrCreate(
            ['email' => $email],
            $data
        );
        // если пользователь авторизован — обновляем настройки уведомлений
        if ($user) {
            $userSettings = UserSetting::firstOrCreate(
                ['user_id' => $user->id],
                [] // если нет — создаём
            );

            $userSettings->update([
                'email_notifications' => true,
            ]);
        }

        if ($user) {
            $bitrixSubscriptionService->syncUser($user);
        } else {
            $bitrixSubscriptionService->syncEmailOnly($email, $validated['name'] ?? null, true);
        }

        return back()->with('success', 'Вы подписались на рассылку!');
    }
}
