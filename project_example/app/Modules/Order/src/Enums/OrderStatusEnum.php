<?php

namespace App\Modules\Order\src\Enums;

enum OrderStatusEnum: string
{
    case New = 'new';
    case InProcessing = 'in_processing';
    case AwaitingPayment = 'awaiting_payment';
    case Paid = 'paid';
    case Confirmed = 'confirmed';
    case InWork = 'in_work';
    case Shipped = 'shipped';
    case ReadyForPickup = 'ready_for_pickup';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function toString(): string
    {
        return match ($this) {
            self::New => 'Новый',
            self::InProcessing => 'В обработке',
            self::AwaitingPayment => 'Ожидает оплаты',
            self::Paid => 'Оплачен',
            self::Confirmed => 'Подтвержден',
            self::InWork => 'В работе/в сборке',
            self::Shipped => 'Отправлен/в пути',
            self::ReadyForPickup => 'Готов к выдаче',
            self::Completed => 'Выполнен',
            self::Cancelled => 'Отменён',
        };
    }

    public static function toArray(): array
    {
        return [
            self::New->value => 'Новый',
            self::InProcessing->value => 'В обработке',
            self::AwaitingPayment->value => 'Ожидает оплаты',
            self::Paid->value => 'Оплачен',
            self::Confirmed->value => 'Подтвержден',
            self::InWork->value => 'В работе/в сборке',
            self::Shipped->value => 'Отправлен/в пути',
            self::ReadyForPickup->value => 'Готов к выдаче',
            self::Completed->value => 'Выполнен',
            self::Cancelled->value => 'Отменён',
        ];
    }

}
