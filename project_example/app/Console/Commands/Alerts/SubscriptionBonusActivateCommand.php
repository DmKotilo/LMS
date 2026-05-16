<?php

namespace App\Console\Commands\Alerts;

use Alert\Enums\AlertTypeEnum;
use Alert\Models\Alert;
use App\Modules\Bonus\Bonus\src\Enums\BonusStatusEnum;
use App\Modules\Bonus\Bonus\src\Enums\BonusTypeEnum;
use Bonus\Models\UserBonusHistory;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SubscriptionBonusActivateCommand extends Command
{
    protected $signature = 'notifications:subscription-bonus-activate';

    protected $description = 'Создание алертов о бонусах за подписку после активации начислений';

    public function handle(): int
    {
        $now = Carbon::now();
        $comment = 'Бонус за подписку на рассылку';

        $accruals = UserBonusHistory::query()
            ->with('user')
            ->whereNull('order_id')
            ->where('type', BonusTypeEnum::Accrual)
            ->where('status', BonusStatusEnum::Active)
            ->where('comment', $comment)
            ->where('active_date', '<=', $now)
            ->get();

        $createdCount = 0;

        foreach ($accruals as $history) {
            $user = $history->user;

            if (!$user) {
                continue;
            }

            $alreadyExists = Alert::query()
                ->where('user_id', $user->id)
                ->where('type', AlertTypeEnum::SubscriptionBonus)
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            $bonusAmount = $history->amount;

            if ($bonusAmount <= 0) {
                continue;
            }

            $expiresDate = $history->expires_at
                ? Carbon::parse($history->expires_at)->format('d.m.Y')
                : $now->copy()->addDays(90)->format('d.m.Y');

            Alert::create([
                'user_id' => $user->id,
                'type' => AlertTypeEnum::SubscriptionBonus,
                'title' => 'alerts.subscribeBonusTitle',
                'message' => 'alerts.subscribeBonusMessage',
                'button_text' => 'alerts.buttonDetails',
                'button_link' => route('user.profile.bonus.history'),
                'bonus_count' => $bonusAmount,
                'bonus_message' => 'alerts.subscribeBonusCount',
                'bonus_date' => $expiresDate,
                'is_read' => false,
            ]);

            $createdCount++;
        }

        $this->info("Создано алертов о бонусах за подписку: {$createdCount}");

        return Command::SUCCESS;
    }
}

