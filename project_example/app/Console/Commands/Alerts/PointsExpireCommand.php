<?php

namespace App\Console\Commands\Alerts;

use Alert\Enums\AlertTypeEnum;
use Alert\Models\Alert;
use App\Modules\Bonus\Bonus\src\Enums\BonusStatusEnum;
use App\Modules\Bonus\Bonus\src\Enums\BonusTypeEnum;
use Carbon\Carbon;
use Illuminate\Console\Command;
use User\Models\User;

class PointsExpireCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:points-expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Уведомления о баллах, срок действия которых скоро истекает';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        // Находим пользователей с активными бонусами, у которых есть expires_at
        $users = User::whereHas('bonusHistories', function($q) use ($now) {
            $q->where('type', BonusTypeEnum::Accrual)
                ->where('status', BonusStatusEnum::Active)
                ->where('remaining_amount', '>', 0)
                ->whereNotNull('expires_at')
                ->where('expires_at', '>', $now);
        })->get();

        foreach ($users as $user) {
            // Получаем ближайшую дату истечения и суммируем ВСЕ бонусы с этой датой
            // НЕ путать с FIFO при списании (там списываем по active_date)
            $closestExpiryDate = $user->bonusHistories()
                ->where('type', BonusTypeEnum::Accrual)
                ->where('status', BonusStatusEnum::Active)
                ->where('remaining_amount', '>', 0)
                ->whereNotNull('expires_at')
                ->where('expires_at', '>', $now)
                ->orderBy('expires_at', 'asc')
                ->value('expires_at');

            if (!$closestExpiryDate) {
                continue;
            }

            // Суммируем ВСЕ бонусы, которые истекают в эту дату
            $bonusesExpiringCount = $user->bonusHistories()
                ->where('type', BonusTypeEnum::Accrual)
                ->where('status', BonusStatusEnum::Active)
                ->where('remaining_amount', '>', 0)
                ->where('expires_at', $closestExpiryDate)
                ->sum('remaining_amount');

            $expireDate = Carbon::parse($closestExpiryDate);
            $daysLeft = max(0, $now->diffInDays($expireDate, false));
            $notifyDays = [14, 1];
            
            foreach ($notifyDays as $notifyDay) {
                if ($daysLeft === $notifyDay) {
                    $alreadySent = Alert::where('user_id', $user->id)
                        ->where('type', AlertTypeEnum::PointsExpire)
                        ->whereDate('created_at', '>=', $now->subDay())
                        ->where('days_before_expire', $notifyDay)
                        ->exists();

                    if (!$alreadySent) {
                        Alert::create([
                            'user_id' => $user->id,
                            'type' => AlertTypeEnum::PointsExpire,
                            'bonus_count' => $bonusesExpiringCount,
                            'bonus_message' => 'alerts.pointsExpireBonusMessage',
                            'title' => "alerts.pointsExpireTitle",
                            'message' => "alerts.pointsExpireMessage",
                            'button_text' => "alerts.buttonDetails",
                            'button_link' => route('user.profile.bonus.history'),
                            'days_before_expire' => $notifyDay,
                        ]);
                    }
                }
            }
        }
    }
}
