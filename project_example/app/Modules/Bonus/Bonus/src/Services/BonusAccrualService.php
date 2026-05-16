<?php

namespace App\Modules\Bonus\Bonus\src\Services;

use App\Modules\Bonus\Bonus\src\Enums\BonusStatusEnum;
use App\Modules\Bonus\Bonus\src\Enums\BonusTypeEnum;
use App\Modules\Cart\src\Models\CartItem;
use App\Modules\GlobalPromotion\src\Enums\GlobalPromotionTypeEnum;
use Bonus\Models\BonusCard;
use Bonus\Models\UserBonusHistory;
use GlobalPromotion\Models\GlobalPromotion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Order\Enums\OrderDeliveryTypeEnum;
use Order\Models\Order;
use Product\Models\Product;
use User\Models\User;

class BonusAccrualService
{
    /**
     * Начислить бонусы и кэшбек пользователю за заказ
     *
     * @param Order $order
     * @param \Illuminate\Support\Collection|CartItem[] $cartItems
     * @return array ['bonus' => int, 'cashback' => float]
     */
    public function accrueForOrder(Order $order, $cartItems): array
    {

        $totalBonus = 0;
        $totalCashback = 0;

        $user = $order->user;

        $bonusCard = $user->card;
        if (!$bonusCard) {
            $bonusCard = $this->generateCard($user->id);
        }

        // 1. Начисляем бонусы по УРОВНЮ ЛОЯЛЬНОСТИ пользователя
        // Не начисляем повторно, если уже есть записи за этот заказ
        if (UserBonusHistory::where('order_id', $order->id)->exists()) {
            return [
                'bonus' => 0,
                'cashback' => 0,
            ];
        }

        $loyaltyCashback = $this->calculateLoyaltyCashback($user, $order);
        if ($loyaltyCashback > 0) {
            $this->storeBonusHistory($user, $bonusCard, $loyaltyCashback, 'loyalty_cashback', $order);
            $totalCashback += $loyaltyCashback;
        }

        $pickupBonus = $this->calculatePickupBonus($order);
        if ($pickupBonus > 0) {
            $this->storeBonusHistory($user, $bonusCard, $pickupBonus, 'pickup_bonus', $order);
            $totalBonus += $pickupBonus;
        }

        // 2. Начисляем бонусы по ГЛОБАЛЬНЫМ АКЦИЯМ
        $activePromotions = GlobalPromotion::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->get();
        foreach ($activePromotions as $promotion) {
            switch ($promotion->type->value) {
                case 'bonus':
                    $amount = $this->calculateBonus($promotion, $order);
                    if ($amount > 0) {
                        $this->storeBonusHistory($user, $bonusCard, $amount, 'bonus', $order, $promotion);
                        $totalBonus += $amount;
                    }
                    break;

                case 'cashback':
                    $amount = $this->calculateCashback($promotion, $order);
                    if ($amount > 0) {
                        $this->storeBonusHistory($user, $bonusCard, $amount, 'cashback', $order, $promotion);
                        $totalCashback += $amount;
                    }
                    break;

                case 'product_cashback':
                    $amount = $this->calculateProductCashback($promotion, $cartItems);
                    if ($amount > 0) {
                        $this->storeBonusHistory($user, $bonusCard, $amount, 'cashback', $order, $promotion);
                        $totalCashback += $amount;
                    }
                    break;
            }
        }

        // Округляем итоговые значения до целых чисел математически
        return [
            'bonus' => (int) round($totalBonus),
            'cashback' => (int) round($totalCashback),
        ];
    }

    /**
     * Рассчитать кешбек по уровню лояльности пользователя
     *
     * @param User $user
     * @param Order $order
     * @return int
     */
    protected function calculateLoyaltyCashback(User $user, Order $order): int
    {
        $userLoyaltyLevel = $user->loyaltyLevel;
        if (!$userLoyaltyLevel || !$userLoyaltyLevel->loyaltyLevel) {
            return 0;
        }

        $loyaltyLevel = $userLoyaltyLevel->loyaltyLevel;
        $discountPercent = $loyaltyLevel->discount_percent ?? 0;

        if ($discountPercent <= 0) {
            return 0;
        }

        // Округляем до целого числа математически (4.5 = 5, 4.4 = 4)
        return (int) round($order->price_final * $discountPercent / 100);
    }

    protected function calculateBonus(GlobalPromotion $promotion, Order $order): int
    {
        $orderAmount = (float) $order->price_final;
        $minAmount = $promotion->min_order_sum_for_bonus !== null
            ? (float) $promotion->min_order_sum_for_bonus
            : null;
        $maxAmount = $promotion->max_order_sum_for_bonus !== null
            ? (float) $promotion->max_order_sum_for_bonus
            : null;

        if ($minAmount !== null && $orderAmount < $minAmount) {
            return 0;
        }

        if ($maxAmount !== null && $orderAmount > $maxAmount) {
            return 0;
        }

        return $promotion->bonus_points ?? 0;
    }

    protected function calculatePickupBonus(Order $order): int
    {
        if ($order->delivery_type !== OrderDeliveryTypeEnum::pickup) {
            return 0;
        }

        $city = $order->pickupLocation?->city_relation;

        if (!$city) {
            return 0;
        }

        return (int) ($city->pickup_bonus_points ?? 0);
    }

    protected function calculateCashback(GlobalPromotion $promotion, Order $order): int
    {
        // Проверяем минимальную сумму заказа для кэшбека
        if ($promotion->min_order_sum_for_cashback !== null) {
            if ($order->price_final < (float) $promotion->min_order_sum_for_cashback) {
                return 0;
            }
        }

        $percent = $promotion->cashback_percent ?? 0;
        if ($percent <= 0) {
            return 0;
        }

        // Округляем до целого числа математически (4.5 = 5, 4.4 = 4)
        return (int) round($order->price_final * $percent / 100);
    }

    protected function calculateProductCashback(GlobalPromotion $promotion, $cartItems): int
    {
        $percent = $promotion->cashback_percent ?? 0;
        $bonus = 0;
        foreach ($cartItems as $cartItem) {
            // Пропускаем подарки - за них не начисляем бонусы
            $isGift = data_get($cartItem, 'is_gift', false) || data_get($cartItem, 'isGift', false);
            if ($isGift) {
                continue;
            }

            $item = $cartItem->item instanceof \Illuminate\Http\Resources\Json\JsonResource
                ? $cartItem->item->resource
                : $cartItem->item;

            if ($item instanceof Product || $item instanceof \Combo\Models\Combo) {
                // Используем правильное имя связи Globalpromotion (с большой буквы G)
                $promotions = $item->relationLoaded('Globalpromotion')
                    ? $item->Globalpromotion
                    : $item->Globalpromotion()->get();

                if ($promotions->pluck('id')->contains($promotion->id)) {
                    $bonus += $this->getItemTotalPrice($cartItem) * $percent / 100;
                }
            }
        }
        // Округляем до целого числа математически (4.5 = 5, 4.4 = 4)
        return (int) round($bonus);
    }


    protected function storeBonusHistory(User $user, BonusCard $card, $amount, string $type, Order $order, ?GlobalPromotion $promotion = null)
    {
        // Округляем до целого числа математически перед сохранением (4.5 = 5, 4.4 = 4)
        $amount = (int) round($amount);
        
        $activeDate = Carbon::now()->addHours(1); // ВРЕМЕННО: изменено с 24 часов на 1 час для тестирования
        $expiresAt = $activeDate->copy()->addDays(90);

        // Формируем информативный комментарий
        $comment = "Начисление за заказ #{$order->id}: {$type}";
        
        if ($promotion) {
            $promotionInfo = [];
            $promotionInfo[] = "ID акции: {$promotion->id}";
            $promotionInfo[] = "Название: {$promotion->title}";
            
            if ($promotion->type->value === 'bonus' && $promotion->bonus_points) {
                $promotionInfo[] = "Бонусы: {$promotion->bonus_points} баллов";
            } elseif (($promotion->type->value === 'cashback' || $promotion->type->value === 'product_cashback') && $promotion->cashback_percent) {
                $promotionInfo[] = "Кэшбек: {$promotion->cashback_percent}%";
            }
            
            $comment .= " (" . implode(", ", $promotionInfo) . ")";
        } elseif ($type === 'loyalty_cashback') {
            $userLoyaltyLevel = $user->loyaltyLevel?->loyaltyLevel;
            if ($userLoyaltyLevel && $userLoyaltyLevel->discount_percent) {
                $comment .= " (Уровень лояльности: {$userLoyaltyLevel->discount_percent}%)";
            }
        } elseif ($type === 'pickup_bonus') {
            $city = $order->pickupLocation?->city_relation;
            if ($city && $city->pickup_bonus_points) {
                $comment .= " (Город: {$city->name}, бонусы: {$city->pickup_bonus_points})";
            }
        }

        UserBonusHistory::create([
            'user_id' => $user->id,
            'bonus_card_id' => $card->id,
            'order_id' => $order->id,
            'amount' => $amount,
            'remaining_amount' => $amount,
            'type' => BonusTypeEnum::Accrual,
            'status' => BonusStatusEnum::Active,
            'active_date' => $activeDate,
            'expires_at' => $expiresAt,
            'is_manual' => false,
            'reason' => null,
            'admin_id' => null,
            'comment' => $comment,
        ]);

    }

    protected function getItemTotalPrice($cartItem): float
    {
        if (isset($cartItem->total_price)) {
            return (float) $cartItem->total_price;
        }

        if (isset($cartItem->price)) {
            return (float) $cartItem->price;
        }

        $quantity = data_get($cartItem, 'quantity', 1);
        $priceForUnit = data_get($cartItem, 'price_for_unit', 0);

        return (float) $priceForUnit * (float) $quantity;
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

    protected function generateCardNumber(int $userId): string
    {
        return $this->generateCard($userId)->number;
    }
}


