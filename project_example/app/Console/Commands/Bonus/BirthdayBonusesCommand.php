<?php

namespace App\Console\Commands\Bonus;

use User\Models\User;
use Bonus\Services\BonusService;
use Illuminate\Console\Command;
use Setting\Models\Setting;
use App\Notifications\BirthdayBonusNotification;
use Alert\Models\Alert;
use Alert\Enums\AlertTypeEnum;

class BirthdayBonusesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bonuses:birthday';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Начисление бонусов пользователям за 2 дня до дня рождения';

    /**
     * Execute the console command.
     */
    public function handle(BonusService $bonusService): int
    {
        $bonusAmount = Setting::where('key', 'birthday_bonus')->first();
        $bonusValue = $bonusAmount ? $bonusAmount->value : 0;

        if ($bonusValue <= 0) {
            $this->info('Birthday bonus amount is not set or is zero');
            return Command::SUCCESS;
        }

        $birthdayUsers = User::birthdayIn2Days()->get();

        foreach ($birthdayUsers as $user) {
            $bonusHistory = $bonusService->awardBirthdayBonus($user, $bonusValue);

            if ($bonusHistory) {
                // Отправка email
                if ($user->email && $user->email_verified_at) {
                    $user->notify(new BirthdayBonusNotification($bonusValue));
                }

                Alert::create([
                    'user_id' => $user->id,
                    'type' => AlertTypeEnum::BirthdayBonus,
                    'title' => 'alerts.birthdayBonusTitle',
                    'message' => "alerts.birthdayBonusMessage",
                    'button_text' => 'alerts.buttonDetails',
                    'button_link' => route('user.profile.bonus.history'),
                    'bonus_count' => $bonusValue,
                    'bonus_message' => "alerts.birthdayBonusCount",
                    'bonus_date' => now()->addDays(30)->format('d.m.Y'),
                    'is_read' => false,
                ]);

                $this->info("Начислено {$bonusValue} бонусов пользователю {$user->id}");
            }
        }

        $this->info("Обработано пользователей: " . $birthdayUsers->count());

        return Command::SUCCESS;
    }
}
