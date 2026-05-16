<?php

namespace Alert\Database\Seeders;
use Alert\Enums\AlertTypeEnum;
use Alert\Models\Alert;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use User\Models\User;

class AlertSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        if (!$users) {
            $this->command->warn('Нет пользователей для создания уведомлений');
            return;
        }
            foreach ($users as $user){
                Alert::create([
                    'user_id' => $user->id,
                    'type' => AlertTypeEnum::PurchaseBonus,
                    'title' => trans_choice('alerts.purchaseBonusTitle', 350, ['n' => 350]),
                    'bonus_message' => 'alerts.purchaseBonusTitleTwo',
                    'message' => __('alerts.purchaseBonusMessage'),
                    'bonus_date' => Carbon::now()->addDays(90)->format('d.m.Y'),
                    'button_text' => __('alerts.buttonDetails'),
                    'button_link' => route('user.profile.bonus.history'),
                    'bonus_count' => 350,
                ]);

                Alert::create([
                    'user_id' => $user->id,
                    'type' => AlertTypeEnum::ReviewReply,
                    'title' => __('alerts.reviewReplyTitle'),
                    'message' => __('alerts.reviewReplyMessage'),
                    'button_text' => __('alerts.buttonDetails'),
                    'button_link' => route('user.profile.reviews'),
                ]);

                Alert::create([
                    'user_id' => $user->id,
                    'type' => AlertTypeEnum::CartReminder,
                    'title' => __('alerts.cartReminderTitle'),
                    'message' => __('alerts.cartReminderMessage'),
                    'button_text' => __('alerts.buttonGoToCart'),
                    'button_link' => route('cart.index'),
                ]);

                Alert::create([
                    'user_id' => $user->id,
                    'type' => AlertTypeEnum::PointsExpire,
                    'title' => trans_choice('alerts.pointsExpireTitle', 500, ['n' => 500]),
                    'message' => __('alerts.pointsExpireMessage'),
                    'bonus_message' => __('alerts.pointsExpireBonusMessage'),
                    'button_text' => __('alerts.buttonDetails'),
                    'button_link' => route('user.profile.bonus.history'),
                    'bonus_count' => 500,
                ]);
            }


    }
}
