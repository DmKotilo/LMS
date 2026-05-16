<?php

namespace Order\Observers;

use App\Modules\Order\src\Enums\OrderStatusEnum;
use App\Modules\Bonus\Bonus\src\Services\BonusAccrualService;
use App\Modules\Bonus\Bonus\src\Enums\BonusTypeEnum;
use Alert\Models\Alert;
use Alert\Enums\AlertTypeEnum;
use Bonus\Models\UserBonusHistory;
use Bonus\Services\BonusService;
use Illuminate\Support\Facades\Log;
use Loyalty\Services\LoyaltyService;
use Order\Models\Order;
use Illuminate\Support\Carbon;

class OrderObserver
{
    /**
     * Обработчик события создания заказа
     * Интеграция с Bitrix24 перенесена в контроллер после создания всех товаров
     * Observer оставлен для возможных будущих расширений
     */
    public function created(Order $order): void
    {
        // Интеграция с Bitrix24 вызывается напрямую из контроллера после создания всех товаров
        // чтобы гарантировать наличие всех позиций заказа
        Log::channel('bitrix24')->info('Order created event triggered', [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'phone' => $order->phone,
            'email' => $order->email,
            'price_final' => $order->price_final,
            'note' => 'Bitrix24 integration will be called from controller after all items are created'
        ]);
    }


    public function updating(Order $order): void
    {
        if ($order->isDirty('status')) {
            $oldStatusRaw = $order->getOriginal('status');
            $oldStatusEnum = $oldStatusRaw instanceof OrderStatusEnum
                ? $oldStatusRaw
                : (is_string($oldStatusRaw) ? OrderStatusEnum::tryFrom($oldStatusRaw) : null);

            $newStatusEnum = $order->status instanceof OrderStatusEnum
                ? $order->status
                : (is_string($order->status) ? OrderStatusEnum::tryFrom($order->status) : null);

            // Предотвращаем возврат из Completed в более ранние статусы (кроме Cancelled)
            if ($oldStatusEnum === OrderStatusEnum::Completed
                && $newStatusEnum !== OrderStatusEnum::Completed
                && $newStatusEnum !== OrderStatusEnum::Cancelled) {



                $order->status = OrderStatusEnum::Completed;


            }
        }
    }

    /**
     * Обработчик события обновления заказа
     * Отправляет уведомление пользователю при изменении статуса
     */
    public function updated(Order $order): void
    {
        if ($order->wasChanged('status')) {
            $oldStatusRaw = $order->getOriginal('status');
            $oldStatusEnum = $oldStatusRaw instanceof OrderStatusEnum
                ? $oldStatusRaw
                : (is_string($oldStatusRaw) ? OrderStatusEnum::tryFrom($oldStatusRaw) : null);

            $newStatusEnum = $order->status instanceof OrderStatusEnum
                ? $order->status
                : (is_string($order->status) ? OrderStatusEnum::tryFrom($order->status) : null);

            $oldStatusValue = $oldStatusEnum?->value ?? (is_string($oldStatusRaw) ? $oldStatusRaw : 'NULL');
            $newStatusValue = $newStatusEnum?->value ?? (is_string($order->status) ? $order->status : 'NULL');

            if ($order->user) {
                $order->user->notify(
                    new \App\Notifications\OrderStatusChangedNotification(
                        $order,
                        $oldStatusEnum ?? $newStatusEnum,
                        $newStatusEnum ?? OrderStatusEnum::InProcessing
                    )
                );


            } else {

            }

            if ($newStatusEnum === OrderStatusEnum::Paid) {
                $this->handleOrderPaid($order);
            }

            if ($newStatusEnum === OrderStatusEnum::Cancelled) {
                $this->handleOrderCancelled($order, $oldStatusEnum);
            }

            // Начисляем бонусы и обновляем уровень, когда заказ завершён (Completed)
            if ($newStatusEnum === OrderStatusEnum::Completed) {
                $this->handleOrderCompleted($order);
            }
        } else {

        }
    }

    protected function handleOrderPaid(Order $order): void
    {
        // Списание бонусов происходит в OrderController при создании заказа
    }

    protected function handleOrderCancelled(Order $order, ?OrderStatusEnum $oldStatus = null): void
    {
        try {
            $order->loadMissing('user');

            if (!$order->user) {
                Log::warning('Cannot process cancelled order: order has no user', ['order_id' => $order->id]);
                return;
            }

            $bonusService = app(BonusService::class);

            // возвращаем списанные бонусы (если они были потрачены при создании заказа)
            if ($order->bonus_spent > 0) {
                $bonusService->refundBonusesForOrder($order);


            }

            // Если заказ ранее был выполнен (Completed), списываем начисленные бонусы
            // даже если они еще не активны (active_date в будущем)
            if ($oldStatus === OrderStatusEnum::Completed) {
                $bonusService->cancelAccruedBonusesForOrder($order);


                $loyaltyService = app(LoyaltyService::class);
                $updatedLoyaltyLevel = $loyaltyService->updateUserLoyaltyLevel($order->user);


            }
        } catch (\Throwable $e) {

        }
    }

    protected function handleOrderCompleted(Order $order): void
    {
        try {
            $order->loadMissing([
                'items.item',
                'user.card',
                'pickupLocation.city_relation'
            ]);

            // Загружаем связи Globalpromotion для товаров и комбо
            foreach ($order->items as $item) {
                if ($item->item) {
                    $item->item->loadMissing('Globalpromotion');
                }
            }

            if (!$order->user) {
                return;
            }

            $bonusAccrualService = app(BonusAccrualService::class);
            $bonusResult = $bonusAccrualService->accrueForOrder($order, $order->items);

            // Обновляем уровень лояльности после выполнения заказа
            // Важно: убеждаемся, что заказ сохранен в базе и обновляем пользователя
            $order->refresh(); // Обновляем заказ из базы, чтобы убедиться, что статус сохранен
            $order->user->refresh(); // Обновляем пользователя из базы

            $loyaltyService = app(LoyaltyService::class);
            $updatedLoyaltyLevel = $loyaltyService->updateUserLoyaltyLevel($order->user);



        } catch (\Throwable $e) {

        }
    }
}
