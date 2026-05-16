<?php

namespace Bonus\Database\Seeders;
use App\Modules\Bonus\Bonus\src\Enums\BonusStatusEnum;
use App\Modules\Bonus\Bonus\src\Enums\BonusTypeEnum;
use Bonus\Models\BonusCard;
use Bonus\Models\UserBonusAllocation;
use Bonus\Models\UserBonusHistory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use User\Models\User;

class BonusSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            $bonusCard = BonusCard::inRandomOrder()->first();

            // Шаг 1: Создаём начисления (Accrual)
            $accruals = [];
            for ($i = 0; $i < 15; $i++) {
                $amount = rand(50, 500);
                $active_date = Carbon::now()->subDays(rand(0, 60));
                $expires_at = $active_date->copy()->addDays(90);
                
                // Определяем статус на основе даты истечения
                $isExpired = $expires_at->isPast();
                
                // Приоритет определения статуса:
                // 1. Если срок истек -> Expired (независимо от остатка, бонус сгорел по времени)
                // 2. Если срок не истек и остаток = 0 -> Spent (потрачен клиентом/списан админом)
                // 3. Если срок не истек и остаток > 0 -> Active (доступен для использования)
                
                if ($isExpired) {
                    // Истекшие по сроку - могут иметь любой остаток
                    $status = BonusStatusEnum::Expired->value;
                    $remaining_amount = rand(0, $amount); // Случайный остаток
                } else {
                    // Не истекшие - создаём только активные с полной суммой
                    // (Spent будет создаваться автоматически при списании)
                    $status = BonusStatusEnum::Active->value;
                    $remaining_amount = $amount;
                }

                $accrual = UserBonusHistory::create([
                    'user_id' => $user->id,
                    'bonus_card_id' => $bonusCard?->id,
                    'amount' => $amount,
                    'type' => BonusTypeEnum::Accrual->value,
                    'status' => $status,
                    'active_date' => $active_date,
                    'expires_at' => $expires_at,
                    'remaining_amount' => $remaining_amount,
                ]);
                
                // Сохраняем только активные начисления с остатком для дальнейшего списания
                // (Active статус подразумевает remaining_amount > 0)
                if ($status === BonusStatusEnum::Active->value) {
                    $accruals[] = $accrual;
                }
            }

            // Шаг 2: Создаём списания (Withdrawal) с привязкой к начислениям
            for ($i = 0; $i < 5; $i++) {
                if (empty($accruals)) {
                    break; // Нет активных начислений для списания
                }

                $withdrawalAmount = rand(10, 200);
                $active_date = Carbon::now()->subDays(rand(0, 30));

                $withdrawal = UserBonusHistory::create([
                    'user_id' => $user->id,
                    'bonus_card_id' => $bonusCard?->id,
                    'amount' => $withdrawalAmount,
                    'type' => BonusTypeEnum::Withdrawal->value,
                    'status' => BonusStatusEnum::Spent->value,
                    'active_date' => $active_date,
                    'expires_at' => null,
                    'remaining_amount' => null,
                ]);

                // Шаг 3: Распределяем списание по начислениям (FIFO)
                $remainingToWithdraw = $withdrawalAmount;
                
                foreach ($accruals as $key => $accrual) {
                    if ($remainingToWithdraw <= 0) {
                        break;
                    }

                    if ($accrual->remaining_amount <= 0) {
                        continue;
                    }

                    // Сколько можем списать из этого начисления
                    $amountToDeduct = min($remainingToWithdraw, $accrual->remaining_amount);

                    // Создаём запись в user_bonus_allocations
                    UserBonusAllocation::create([
                        'withdrawal_id' => $withdrawal->id,
                        'accrual_id' => $accrual->id,
                        'amount' => $amountToDeduct,
                    ]);

                    // Обновляем remaining_amount в начислении
                    $accrual->remaining_amount -= $amountToDeduct;
                    
                    // Если remaining_amount стал 0, меняем статус
                    if ($accrual->remaining_amount <= 0) {
                        $accrual->remaining_amount = 0;
                        
                        // Статус Spent только если срок не истек
                        // Если истек срок -> остается Expired
                        if ($accrual->expires_at && $accrual->expires_at->isPast()) {
                            $accrual->status = BonusStatusEnum::Expired->value;
                        } else {
                            $accrual->status = BonusStatusEnum::Spent->value;
                        }
                        
                        $accrual->save();
                        unset($accruals[$key]);
                    } else {
                        $accrual->save();
                    }

                    $remainingToWithdraw -= $amountToDeduct;
                }
            }
        }
    }
}
