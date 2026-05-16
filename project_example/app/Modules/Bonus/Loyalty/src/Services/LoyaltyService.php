<?php

namespace Loyalty\Services;

use App\Modules\Order\src\Enums\OrderStatusEnum;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Loyalty\Models\LoyaltyLevel;
use User\Models\User;
use User\Models\UserLoyaltyLevel;

class LoyaltyService
{
    /**
     * Обновить уровень лояльности пользователя на основе суммы заказов
     *
     * @param User $user
     * @return UserLoyaltyLevel|null
     */
    public function updateUserLoyaltyLevel(User $user): ?UserLoyaltyLevel
    {
        $levels = LoyaltyLevel::orderBy('coefficient', 'asc')->get();

        if ($levels->isEmpty()) {
            return null;
        }

        $defaultLevel = $levels->first();

        // Используем фиксированные 90 дней (3 месяца) для расчета уровня лояльности
        $durationDays = 90;

        // сумма заказов за период (только выполненные заказы)
        $now = Carbon::now();

        // Принудительно обновляем кеш связей пользователя, чтобы получить актуальные данные
        $user->unsetRelation('orders');

        // Используем прямой запрос к базе для гарантии актуальных данных
        $totalSpent = (float) DB::table('orders')
            ->where('user_id', $user->id)
            ->where('status', OrderStatusEnum::Completed->value)
            ->where('created_at', '>=', $now->copy()->subDays($durationDays))
            ->where('created_at', '<=', $now)
            ->sum('price_final');

        // Логирование для отладки
        $completedOrdersCount = $user->orders()
            ->where('status', OrderStatusEnum::Completed->value)
            ->where('created_at', '>=', $now->copy()->subDays($durationDays))
            ->where('created_at', '<=', $now)
            ->count();


        // Определяем подходящий уровень на основе суммы заказов
        // coefficient - это пороговое значение (максимальная сумма для уровня)
        // Rare: от 0 до 39999 (coefficient = 39999)
        // Medium: от 40000 до 69999 (coefficient = 69999)
        // Well Done: от 70000 и выше (coefficient = 70000)
        //
        // Логика: идем по уровням в порядке возрастания coefficient
        // Находим первый уровень, где totalSpent <= coefficient
        // Если totalSpent больше всех coefficient, берем последний уровень
        $appropriateLevel = $defaultLevel;
        $previousCoefficient = 0;

        foreach ($levels as $level) {
            // Если сумма попадает в диапазон этого уровня
            // (больше предыдущего порога и меньше или равна текущему)
            // Приводим к float для корректного сравнения
            $levelCoefficient = (float) $level->coefficient;
            if ($totalSpent > $previousCoefficient && $totalSpent <= $levelCoefficient) {
                $appropriateLevel = $level;
                break;
            }
            $previousCoefficient = $levelCoefficient;
        }

        // Если сумма больше максимальной для всех уровней, берем последний (самый высокий)
        if ($totalSpent > $levels->last()->coefficient) {
            $appropriateLevel = $levels->last();
        }



        // Получаем или создаем запись уровня лояльности пользователя
        $userLoyaltyLevel = UserLoyaltyLevel::firstOrCreate(
            ['user_id' => $user->id],
            ['loyalty_level_id' => $appropriateLevel->id]
        );

        // Обновляем уровень, если он изменился
        $oldLevelId = $userLoyaltyLevel->loyalty_level_id;
        $oldLevelName = $userLoyaltyLevel->loyaltyLevel?->name;

        if ($userLoyaltyLevel->loyalty_level_id !== $appropriateLevel->id) {
            // Используем update для гарантированного обновления в базе
            $updated = UserLoyaltyLevel::where('id', $userLoyaltyLevel->id)
                ->update(['loyalty_level_id' => $appropriateLevel->id]);

            // Обновляем модель для дальнейшего использования
            $userLoyaltyLevel->loyalty_level_id = $appropriateLevel->id;
            $userLoyaltyLevel->refresh();


        } else {

        }

        // Обновляем связи, чтобы получить актуальные данные
        $userLoyaltyLevel->load('loyaltyLevel');

        return $userLoyaltyLevel;
    }

    /**
     * Получить сумму заказов пользователя за период
     *
     * @param User $user
     * @param int $days
     * @return float
     */
    public function getUserTotalSpent(User $user, int $days = 90): float
    {
        $now = Carbon::now();

        return $user->orders()
            ->where('status', OrderStatusEnum::Completed->value)
            ->where('created_at', '>=', $now->copy()->subDays($days))
            ->where('created_at', '<=', $now)
            ->sum('price_final');
    }
}

