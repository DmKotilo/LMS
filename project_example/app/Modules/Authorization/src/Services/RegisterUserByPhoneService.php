<?php

namespace Authorization\Services;

use App\Modules\Referral\src\Services\ReferralService;
use App\Services\ActivityLogService;
use Authorization\Exceptions\CodeService\CodeServiceException;
use Authorization\Interfaces\Services\VerifyCodeServiceInterface;
use Authorization\Models\VerifyCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use User\Enums\RoleEnum;
use User\Models\User;

class RegisterUserByPhoneService
{

   static function registerUserByPhone($phone , ?string $referralCode = null, ?Request $request = null) : User {
       $normalizedPhone = \App\Helpers\PhoneHelper::formatPhone($phone);
       $user = User::create(['phone' => $normalizedPhone, 'phone_verified_at' => Carbon::now()]);
       if ($referralCode) {
           $referralService = new ReferralService();
           $referralService->processReferral($user, $referralCode);
       }
       Auth::login($user);

       // Логирование регистрации через телефон
       if ($request) {
           $activityLogService = app(ActivityLogService::class);
           $activityLogService->logRegistration($user, $request, true);
       }

       return $user;
   }

}
