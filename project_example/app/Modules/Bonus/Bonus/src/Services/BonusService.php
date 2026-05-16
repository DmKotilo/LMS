<?php

namespace Bonus\Services;

use App\Modules\Bonus\Bonus\src\Enums\BonusManualReasonEnum;
use App\Modules\Bonus\Bonus\src\Enums\BonusStatusEnum;
use App\Modules\Bonus\Bonus\src\Enums\BonusTypeEnum;
use Bonus\Models\BonusCard;
use Bonus\Models\UserBonusAllocation;
use Bonus\Models\UserBonusHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Order\Models\Order;
use User\Models\User;

class BonusService
{
    /**
     * Начисление бонусов пользователю
     *
     * @param User $user Пользователь
     * @param float $amount Сумма начисления
     * @param bool $isManual Ручное начисление
     * @param BonusManualReasonEnum|null $reason Причина (для ручного начисления)
     * @param int|null $adminId ID администратора
     * @param string|null $comment Комментарий
     * @param int|null $bonusCardId ID бонусной карты
     * @return UserBonusHistory
     */
    public function accrueBonus(
        User $user,
        float $amount,
        bool $isManual = false,
        ?BonusManualReasonEnum $reason = null,
        ?int $adminId = null,
        ?string $comment = null,
        ?int $bonusCardId = null,
        ?Order $order = null
    ): UserBonusHistory {
        $activeDate = now()->addHours(1); // ВРЕМЕННО: изменено с 24 часов на 1 час для тестирования
        $expiresAt = $activeDate->copy()->addDays(90);

        // Получаем или создаем бонусную карту, если ее нет
        $bonusCard = $bonusCardId 
            ? BonusCard::find($bonusCardId)
            : ($user->card ?? $this->generateCard($user->id));

        $history = UserBonusHistory::create([
            'user_id' => $user->id,
            'bonus_card_id' => $bonusCard->id,
            'order_id' => $order?->id,
            'amount' => $amount,
            'remaining_amount' => $amount,
            'type' => BonusTypeEnum::Accrual,
            'status' => BonusStatusEnum::Active,
            'active_date' => $activeDate,
            'expires_at' => $expiresAt,
            'is_manual' => $isManual,
            'reason' => $reason,
            'admin_id' => $adminId,
            'comment' => $comment,
        ]);


        return $history;
    }

    /**
     * Завершить ручное начисление (для MoonShine)
     * Обновляет уже созданную запись, добавляя необходимые поля
     *
     * @param UserBonusHistory $bonusHistory Уже созданная запись из MoonShine
     * @param User $user Пользователь
     * @param int|null $adminId ID администратора
     * @return UserBonusHistory
     */
    public function completeManualAccrual(UserBonusHistory $bonusHistory, User $user, ?int $adminId = null): UserBonusHistory
    {
        // Дата активации: если администратор указал - используем, иначе +1 час (ВРЕМЕННО для тестирования)
        $activeDate = $bonusHistory->active_date
            ? \Carbon\Carbon::parse($bonusHistory->active_date)
            : now()->addHours(1); // ВРЕМЕННО: изменено с 24 часов на 1 час для тестирования

        // Дата истечения: если администратор указал - используем, иначе +90 дней от active_date
        $expiresAt = $bonusHistory->expires_at
            ? \Carbon\Carbon::parse($bonusHistory->expires_at)
            : $activeDate->copy()->addDays(90);

        // Получаем или создаем бонусную карту, если ее нет
        $bonusCard = $user->card ?? $this->generateCard($user->id);

        $bonusHistory->update([
            'bonus_card_id' => $bonusCard->id,
            'remaining_amount' => $bonusHistory->amount,
            'active_date' => $activeDate,
            'status' => BonusStatusEnum::Active,
            'expires_at' => $expiresAt,
            'is_manual' => true,  // Устанавливаем здесь, чтобы observer сработал при update
            'admin_id' => $adminId,
        ]);

        return $bonusHistory->fresh();
    }

    /**
     * Списание бонусов у пользователя (FIFO - First In First Out)
     *
     * @param User $user Пользователь
     * @param float $amount Сумма списания
     * @param bool $isManual Ручное списание
     * @param BonusManualReasonEnum|null $reason Причина (для ручного списания)
     * @param int|null $adminId ID администратора
     * @param string|null $comment Комментарий
     * @param Order|null $order Связанный заказ (для автоматического списания)
     * @return UserBonusHistory|null
     * @throws \Exception
     */
    public function withdrawBonus(
        User $user,
        float $amount,
        bool $isManual = false,
        ?BonusManualReasonEnum $reason = null,
        ?int $adminId = null,
        ?string $comment = null,
        ?Order $order = null
    ): ?UserBonusHistory {
        // Проверяем доступный баланс
        $availableBalance = $this->getAvailableBalance($user);

        if ($availableBalance < $amount) {
            throw new \Exception("Недостаточно бонусов для списания. Доступно: {$availableBalance}, запрошено: {$amount}");
        }

        // Создаём запись о списании
        // Withdrawal не имеет remaining_amount (это null, не 0)
        $withdrawal = UserBonusHistory::create([
            'user_id' => $user->id,
            'bonus_card_id' => $user->card?->id,
            'order_id' => $order?->id,
            'amount' => $amount,
            'remaining_amount' => null,
            'type' => BonusTypeEnum::Withdrawal,
            'status' => BonusStatusEnum::Spent,
            'active_date' => now(),
            'expires_at' => null,
            'is_manual' => $isManual,
            'reason' => $reason,
            'admin_id' => $adminId,
            'comment' => $comment,
        ]);

        // Списываем бонусы по FIFO (сначала самые старые активные)
        $this->allocateWithdrawal($user, $withdrawal, $amount);



        return $withdrawal;
    }

    public function refundBonusesForOrder(Order $order): void
    {
        $user = $order->user;

        if (!$user) {
            return;
        }

        $withdrawals = UserBonusHistory::where('order_id', $order->id)
            ->where('type', BonusTypeEnum::Withdrawal)
            ->where('status', BonusStatusEnum::Spent)
            ->with(['sources.accrual'])
            ->get();

        if ($withdrawals->isEmpty()) {
            return;
        }

        foreach ($withdrawals as $withdrawal) {
            foreach ($withdrawal->sources as $allocation) {
                $originalAccrual = $allocation->accrual;

                if (!$originalAccrual) {
                    continue;
                }

                if ($allocation->amount <= 0) {
                    continue;
                }

                $expiresAt = $originalAccrual->expires_at
                    ? $originalAccrual->expires_at->copy()->subDay()
                    : null;

                UserBonusHistory::create([
                    'user_id' => $user->id,
                    'bonus_card_id' => $user->card?->id,
                    'order_id' => $order->id,
                    'amount' => $allocation->amount,
                    'remaining_amount' => $allocation->amount,
                    'type' => BonusTypeEnum::Accrual,
                    'status' => BonusStatusEnum::Active,
                    'active_date' => now(),
                    'expires_at' => $expiresAt,
                    'is_manual' => false,
                    'reason' => null,
                    'admin_id' => null,
                    'comment' => "Возврат бонусов за отменённый заказ #{$order->id}",
                ]);
            }

            $withdrawal->status = BonusStatusEnum::Expired;
            $withdrawal->comment = trim(($withdrawal->comment ? $withdrawal->comment . ' ' : '') . "Возврат при отмене заказа #{$order->id}");
            $withdrawal->save();
        }
    }

    /**
     * Списать начисленные бонусы за отмененный заказ
     * Используется когда заказ был выполнен (начислены бонусы), а потом отменен
     *
     * @param Order $order
     * @return void
     */
    public function cancelAccruedBonusesForOrder(Order $order): void
    {
        $user = $order->user;

        if (!$user) {
            return;
        }

    
        $accruals = UserBonusHistory::where('order_id', $order->id)
            ->where('type', BonusTypeEnum::Accrual)
            ->where('status', BonusStatusEnum::Active)
            ->where('remaining_amount', '>', 0)
            ->where('comment', 'not like', 'Возврат бонусов за отменённый заказ%')
            ->lockForUpdate()
            ->get();

        if ($accruals->isEmpty()) {
            return;
        }

        $totalCancelled = 0;

        foreach ($accruals as $accrual) {
            $amountToCancel = $accrual->remaining_amount;

            if ($amountToCancel <= 0) {
                continue;
            }

            $withdrawal = UserBonusHistory::create([
                'user_id' => $user->id,
                'bonus_card_id' => $user->card?->id,
                'order_id' => $order->id,
                'amount' => $amountToCancel,
                'remaining_amount' => null,
                'type' => BonusTypeEnum::Withdrawal,
                'status' => BonusStatusEnum::Spent,
                'active_date' => now(),
                'expires_at' => null,
                'is_manual' => false,
                'reason' => null,
                'admin_id' => null,
                'comment' => "Списание начисленных бонусов за отменённый заказ #{$order->id}",
            ]);

            UserBonusAllocation::create([
                'withdrawal_id' => $withdrawal->id,
                'accrual_id' => $accrual->id,
                'amount' => $amountToCancel,
            ]);

            $accrual->remaining_amount = 0;
            $accrual->status = BonusStatusEnum::Spent;
            $accrual->save();

            $totalCancelled += $amountToCancel;

            Log::info('Начисленные бонусы списаны за отмененный заказ', [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'accrual_id' => $accrual->id,
                'withdrawal_id' => $withdrawal->id,
                'amount' => $amountToCancel,
            ]);
        }

        if ($totalCancelled > 0) {
            Log::info('Все начисленные бонусы списаны за отмененный заказ', [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'total_cancelled' => $totalCancelled,
            ]);
        }
    }

    /**
     * Распределяет списание по начислениям (FIFO)
     *
     * @param User $user
     * @param UserBonusHistory $withdrawal
     * @param float $amount
     * @return void
     */
    protected function allocateWithdrawal(User $user, UserBonusHistory $withdrawal, float $amount): void
    {
        $remainingToWithdraw = $amount;

        // Получаем активные начисления с остатком, отсортированные по дате списания (раньше сгорающие приоритетнее)
        $accruals = UserBonusHistory::where('user_id', $user->id)
            ->where('type', BonusTypeEnum::Accrual)
            ->where('status', BonusStatusEnum::Active)
            ->where('remaining_amount', '>', 0)
            ->where('active_date', '<=', now())
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderByRaw('expires_at ASC NULLS LAST')
            ->orderBy('active_date', 'asc')
            ->orderBy('id', 'asc')  // для детерминированного порядка при одинаковых датах
            ->lockForUpdate()
            ->get();

        foreach ($accruals as $accrual) {
            if ($remainingToWithdraw <= 0) {
                break;
            }

            // Определяем сколько можем списать из этого начисления
            $amountToAllocate = min($accrual->remaining_amount, $remainingToWithdraw);

            // Создаём запись в allocations
            UserBonusAllocation::create([
                'withdrawal_id' => $withdrawal->id,
                'accrual_id' => $accrual->id,
                'amount' => $amountToAllocate,
            ]);

            // Обновляем remaining_amount у начисления
            $accrual->remaining_amount -= $amountToAllocate;

            // Если начисление полностью израсходовано, меняем статус
            if ($accrual->remaining_amount <= 0) {
                $accrual->remaining_amount = 0;

                // Приоритет: если срок истек -> Expired, иначе -> Spent
                if ($accrual->expires_at && $accrual->expires_at->isPast()) {
                    $accrual->status = BonusStatusEnum::Expired;
                } else {
                    $accrual->status = BonusStatusEnum::Spent;
                }
            }

            $accrual->save();

            $remainingToWithdraw -= $amountToAllocate;
        }

        if ($remainingToWithdraw > 0) {
            // Это не должно происходить, т.к. мы проверили баланс заранее
            Log::error('Не удалось полностью распределить списание', [
                'user_id' => $user->id,
                'withdrawal_id' => $withdrawal->id,
                'remaining' => $remainingToWithdraw,
            ]);
        }
    }

    /**
     * Получить доступный баланс пользователя
     *
     * @param User $user
     * @return float
     */
    public function getAvailableBalance(User $user): float
    {
        $balance = UserBonusHistory::where('user_id', $user->id)
            ->where('type', BonusTypeEnum::Accrual)
            ->where('status', BonusStatusEnum::Active)
            ->where('remaining_amount', '>', 0)
            ->where('active_date', '<=', now())
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->sum('remaining_amount');
        
        // Округляем до целого числа
        return round((float) $balance);
    }

    /**
     * Получить общий баланс пользователя (включая неактивированные)
     *
     * @param User $user
     * @return float
     */
    public function getTotalBalance(User $user): float
    {
        $balance = UserBonusHistory::where('user_id', $user->id)
            ->where('type', BonusTypeEnum::Accrual)
            ->where('status', BonusStatusEnum::Active)
            ->where('remaining_amount', '>', 0)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->sum('remaining_amount');
        
        // Округляем до целого числа
        return round((float) $balance);
    }

    /**
     * Истечение бонусов (обрабатывает expires_at)
     * Обновляет статус начислений на Expired
     * Вызывается через планировщик каждый день
     *
     * @return int Количество истекших записей
     */
    public function expireBonuses(): int
    {
        $now = now();
        $expiredCount = 0;

        // 1. Находим истёкшие начисления С ОСТАТКОМ > 0
        $expiredWithRemaining = UserBonusHistory::where('type', BonusTypeEnum::Accrual)
            ->where('status', BonusStatusEnum::Active)
            ->where('remaining_amount', '>', 0)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->orderBy('expires_at', 'asc')
            ->orderBy('id', 'asc')
            ->lockForUpdate()
            ->get();

        foreach ($expiredWithRemaining as $accrual) {
            if ($accrual->remaining_amount <= 0) {
                continue;
            }

            $expiredAmount = $accrual->remaining_amount;

            // Создаём запись о сгорании бонусов (чтобы пользователь видел в истории)
            $expiredWithdrawal = UserBonusHistory::create([
                'user_id' => $accrual->user_id,
                'bonus_card_id' => $accrual->bonus_card_id,
                'amount' => $expiredAmount,
                'remaining_amount' => null,
                'type' => BonusTypeEnum::Withdrawal,
                'status' => BonusStatusEnum::Expired,
                'active_date' => $now,
                'expires_at' => null,
                'is_manual' => false,
                'reason' => null,
                'admin_id' => null,
                'comment' => 'Автоматическое истечение бонусов',
            ]);

            // Создаём allocation
            UserBonusAllocation::create([
                'withdrawal_id' => $expiredWithdrawal->id,
                'accrual_id' => $accrual->id,
                'amount' => $expiredAmount,
            ]);

            $accrual->remaining_amount = 0;
            $accrual->status = BonusStatusEnum::Expired;
            $accrual->save();

            $expiredCount++;

            Log::info('Бонусы истекли по сроку (с остатком)', [
                'accrual_id' => $accrual->id,
                'withdrawal_id' => $expiredWithdrawal->id,
                'user_id' => $accrual->user_id,
                'amount' => $expiredAmount,
                'expired_at' => $now,
            ]);
        }

        // 2. Находим истёкшие начисления БЕЗ ОСТАТКА (remaining_amount = 0 или Spent)
        // У них срок истек, но статус еще Active - нужно изменить на Expired
        $expiredWithoutRemaining = UserBonusHistory::where('type', BonusTypeEnum::Accrual)
            ->where('status',BonusStatusEnum::Active)
            ->where(function ($query) {
                $query->where('remaining_amount', '=', 0)
                    ->orWhereNull('remaining_amount');
            })
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->lockForUpdate()
            ->get();

        foreach ($expiredWithoutRemaining as $accrual) {
            $accrual->status = BonusStatusEnum::Expired;
            $accrual->save();

            $expiredCount++;

            Log::info('Статус изменен на Expired (без остатка)', [
                'accrual_id' => $accrual->id,
                'user_id' => $accrual->user_id,
                'expired_at' => $now,
            ]);
        }

        return $expiredCount;
    }

    /**
     * Получить детальную информацию о балансе пользователя
     *
     * @param User $user
     * @return array
     */
    public function getBalanceDetails(User $user): array
    {
        $now = now();

        // Активные бонусы (доступные сейчас)
        $available = UserBonusHistory::where('user_id', $user->id)
            ->where('type', BonusTypeEnum::Accrual)
            ->where('status', BonusStatusEnum::Active)
            ->where('remaining_amount', '>', 0)
            ->where('active_date', '<=', $now)
            ->where(function ($query) use ($now) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', $now);
            })
            ->sum('remaining_amount');

        // Ожидающие активации (active_date в будущем)
        $pending = UserBonusHistory::where('user_id', $user->id)
            ->where('type', BonusTypeEnum::Accrual)
            ->where('status', BonusStatusEnum::Active)
            ->where('remaining_amount', '>', 0)
            ->where('active_date', '>', $now)
            ->sum('remaining_amount');

        // Истекающие в ближайшие 7 дней
        $expiringSoon = UserBonusHistory::where('user_id', $user->id)
            ->where('type', BonusTypeEnum::Accrual)
            ->where('status', BonusStatusEnum::Active)
            ->where('remaining_amount', '>', 0)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [$now, $now->copy()->addDays(7)])
            ->sum('remaining_amount');

        return [
            'available' => round((float) $available),
            'pending' => round((float) $pending),
            'expiring_soon' => round((float) $expiringSoon),
            'total' => round((float) ($available + $pending)),
        ];
    }

    public function awardBirthdayBonus(User $user, int $amount): ?UserBonusHistory
    {
        if (!$user->birth_date) {
            return null;
        }

        return $this->accrueBonus(
            user: $user,
            amount: $amount,
            comment: 'Бонус в преддверии дня рождения'
        );
    }

    /**
     * Генерирует уникальный номер карты и создает бонусную карту
     *
     * @param int $userId
     * @return BonusCard
     */
    protected function generateCard(int $userId): BonusCard
    {
        do {
            $number = str_pad(random_int(0, 9999999999999999), 16, '0', STR_PAD_LEFT);
        } while (BonusCard::where('number', $number)->exists());

        return BonusCard::create([
            'user_id' => $userId,
            'is_active' => true,
            'number' => $number,
        ]);
    }

}

