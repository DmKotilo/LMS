<?php

namespace App\Modules\Referral\src\Services;

use App\Modules\Bonus\Bonus\src\Enums\BonusStatusEnum;
use App\Modules\Bonus\Bonus\src\Enums\BonusTypeEnum;
use App\Modules\Referral\src\Enums\ReferralBonusTypeEnum;
use Alert\Models\Alert;
use Alert\Enums\AlertTypeEnum;
use Authorization\Exceptions\CodeService\CodeServiceException;
use Authorization\Interfaces\Services\VerifyCodeServiceInterface;
use Authorization\Models\VerifyCode;
use Bonus\Models\UserBonusHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Referral\Models\Referral;
use Referral\Models\ReferralBonusHistory;
use Setting\Models\Setting;
use User\Enums\RoleEnum;
use User\Models\User;

class ReferralService
{
    protected int $invitedBonus;
    protected int $inviterBonus;

    public function __construct()
    {
        $this->invitedBonus = (int) (Setting::where('key', 'referral_reward_invited')->first()?->value ?? 500);
        $this->inviterBonus = (int) (Setting::where('key', 'referral_reward_inviter')->first()?->value ?? 500);
    }

    public function processReferral(User $user, ?string $referralCode = null)
    {
        if (!$referralCode) return;

        $inviterReferral = Referral::where('referral_code', $referralCode)
            ->whereNull('invited_id')
            ->first();

        if (!$inviterReferral) return; // код не найден

        if ($inviterReferral->inviter_id === $user->id) return;

        // проверяем, что пользователь ещё не был приглашён по другой ссылке
        $alreadyInvited = Referral::where('invited_id', $user->id)->exists();
        if ($alreadyInvited) return;

        $inviterReferral->update([
            'invited_id' => $user->id,
            'registered_at' => Carbon::now(),
        ]);
    }


    // Начислить бонус пригласившему после первой покупки
    public function rewardReferralBonuses(User $invitedUser)
    {
        $referral = Referral::with('inviter')
            ->where('invited_id', $invitedUser->id)
            ->first();

        if (!$referral || !$referral->inviter) return;

        if ($referral->first_purchase_at !== null) {
            return;
        }

        $referral->first_purchase_at = Carbon::now();
        $referral->save();

        $inviter = $referral->inviter; // пригласивший
        $now = Carbon::now();
        $activationDate = Carbon::now()->addDay();

        //запись в ReferralBonusHistory (аналитика)
        if (!ReferralBonusHistory::where('referral_id', $referral->id)
            ->where('user_id', $inviter->id)
            ->where('type', ReferralBonusTypeEnum::INVITER)
            ->exists()) {

            ReferralBonusHistory::create([
                'referral_id' => $referral->id,
                'user_id' => $inviter->id,
                'amount' => $this->inviterBonus,
                'type' => ReferralBonusTypeEnum::INVITER,
            ]);
        }

        if (!ReferralBonusHistory::where('referral_id', $referral->id)
            ->where('user_id', $invitedUser->id)
            ->where('type', ReferralBonusTypeEnum::INVITED)
            ->exists()) {

            ReferralBonusHistory::create([
                'referral_id' => $referral->id,
                'user_id' => $invitedUser->id,
                'amount' => $this->invitedBonus,
                'type' => ReferralBonusTypeEnum::INVITED,
            ]);
        }

        // запись в user_bonus_history (для системы бонусов)

        $bonusData = [
            ['user' => $inviter, 'amount' => $this->inviterBonus, 'type' => ReferralBonusTypeEnum::INVITER],
            ['user' => $invitedUser, 'amount' => $this->invitedBonus, 'type' => ReferralBonusTypeEnum::INVITED],
        ];

        foreach ($bonusData as $data) {
            $user = $data['user'];
            $amount = $data['amount'];
            $referralType = $data['type'];

            // Срок действия = дата активации + 90 дней
            $expiresAt = $activationDate->copy()->addDays(90);

            UserBonusHistory::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'remaining_amount' => $amount,
                'type' => BonusTypeEnum::Accrual,
                'status' => BonusStatusEnum::Active,
                'active_date' => $activationDate,
                'expires_at' => $expiresAt,
                'bonus_card_id' => $user->card?->id,
            ]);

            // Создаем алерт для реферального бонуса
            $alertType = $referralType === ReferralBonusTypeEnum::INVITER 
                ? AlertTypeEnum::ReferralFriend 
                : AlertTypeEnum::ReferralRegistration;

            Alert::create([
                'user_id' => $user->id,
                'type' => $alertType,
                'title' => $referralType === ReferralBonusTypeEnum::INVITER 
                    ? 'alerts.referralFriendTitle'
                    : 'alerts.referralRegistrationTitle',
                'message' => $referralType === ReferralBonusTypeEnum::INVITER 
                    ? 'alerts.referralFriendMessage'
                    : 'alerts.referralRegistrationMessage',
                'button_text' => 'alerts.buttonDetails',
                'button_link' => route('user.profile.bonus.history'),
                'bonus_count' => $amount,
                'bonus_message' => $referralType === ReferralBonusTypeEnum::INVITER 
                    ? 'alerts.referralFriendCount'
                    : 'alerts.referralRegistrationCount',
                'bonus_date' => $expiresAt->format('d.m.Y'),
                'is_read' => false,
            ]);
        }
    }
}
