<?php

namespace App\Console\Commands\Alerts;

use Alert\Enums\AlertTypeEnum;
use Alert\Models\Alert;
use App\Modules\Bonus\Bonus\src\Enums\BonusStatusEnum;
use App\Modules\Bonus\Bonus\src\Enums\BonusTypeEnum;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Bonus\Models\UserBonusHistory;
use Order\Models\Order;

class PurchaseBonusActivateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:purchase-bonus-activate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создание алертов о начислении бонусов за заказы после их активации (active_date <= now)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now();

        // Берём все начисления по заказам, которые уже активны по дате
        $accruals = UserBonusHistory::query()
            ->whereNotNull('order_id')
            ->where('type', BonusTypeEnum::Accrual)
            ->where('status', BonusStatusEnum::Active)
            ->where('active_date', '<=', $now)
            ->get()
            ->groupBy('order_id');

        $createdCount = 0;

        foreach ($accruals as $orderId => $orderAccruals) {
            /** @var Order|null $order */
            $order = Order::find($orderId);

            if (!$order || !$order->user) {
                continue;
            }

            $user = $order->user;

            // Не создаём дубликаты алерта по одному и тому же заказу
            $alreadyExists = Alert::query()
                ->where('user_id', $user->id)
                ->where('type', AlertTypeEnum::PurchaseBonus)
                ->where('order_id', $order->id)
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            // Считаем сумму начисленных бонусов/кэшбэка по заказу
            $totalAmount = (int) round($orderAccruals->sum('amount'));

            if ($totalAmount <= 0) {
                continue;
            }

            // Дата истечения — максимальная expires_at среди начислений по заказу
            $expiresAt = $orderAccruals
                ->filter(fn ($history) => $history->expires_at !== null)
                ->max('expires_at');

            $expiresDateFormatted = $expiresAt
                ? Carbon::parse($expiresAt)->format('d.m.Y')
                : $now->copy()->addDays(90)->format('d.m.Y');

            Alert::create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'type' => AlertTypeEnum::PurchaseBonus,
                'title' => 'alerts.purchaseBonusTitle',
                'message' => 'alerts.purchaseBonusMessage',
                'button_text' => 'alerts.buttonDetails',
                'button_link' => route('user.profile.bonus.history'),
                'bonus_count' => $totalAmount,
                'bonus_message' => 'alerts.purchaseBonusTitleTwo',
                'bonus_date' => $expiresDateFormatted,
                'is_read' => false,
            ]);

            $createdCount++;
        }

        $this->info("Создано алертов о начислении бонусов: {$createdCount}");

        return Command::SUCCESS;
    }
}


