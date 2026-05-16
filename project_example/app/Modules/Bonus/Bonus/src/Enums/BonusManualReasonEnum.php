<?php

namespace App\Modules\Bonus\Bonus\src\Enums;

enum BonusManualReasonEnum: string
{
    // Начисление
    case Compensation = 'compensation'; // Компенсация за проблему с заказом
    case SpecialPromotion = 'special_promotion'; // Специальная акция
    case GiftFromManagement = 'gift_from_management'; // Подарок от руководства
    case MarketingCampaign = 'marketing_campaign'; // Маркетинговая кампания
    case SystemErrorCredit = 'system_error_credit'; // Техническая ошибка системы

    // Списание
    case Return = 'return'; // Возврат товара
    case SystemErrorDebit = 'system_error_debit'; // Техническая ошибка
    case RulesViolation = 'rules_violation'; // Нарушение правил программы
    case BalanceAdjustment = 'balance_adjustment'; // Корректировка баланса
    case Other = 'other'; // Другое (с комментарием)

    public function toString(): string
    {
        return match ($this) {
            self::Compensation => 'Компенсация за проблему с заказом',
            self::SpecialPromotion => 'Специальная акция',
            self::GiftFromManagement => 'Подарок от руководства',
            self::MarketingCampaign => 'Маркетинговая кампания',
            self::SystemErrorCredit => 'Техническая ошибка системы',
            self::Return => 'Возврат товара',
            self::SystemErrorDebit => 'Техническая ошибка',
            self::RulesViolation => 'Нарушение правил программы',
            self::BalanceAdjustment => 'Корректировка баланса',
            self::Other => 'Другое (с комментарием)',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Compensation->value => 'Компенсация за проблему с заказом',
            self::SpecialPromotion->value => 'Специальная акция',
            self::GiftFromManagement->value => 'Подарок от руководства',
            self::MarketingCampaign->value => 'Маркетинговая кампания',
            self::SystemErrorCredit->value => 'Техническая ошибка системы',
            self::Return->value => 'Возврат товара',
            self::SystemErrorDebit->value => 'Техническая ошибка',
            self::RulesViolation->value => 'Нарушение правил программы',
            self::BalanceAdjustment->value => 'Корректировка баланса',
            self::Other->value => 'Другое (с комментарием)',
        ];
    }

    /**
     * Группировка причин по типу операции (опционально, для валидации/отображения)
     */
    public static function forCredit(): array
    {
        return [
            self::Compensation->value,
            self::SpecialPromotion->value,
            self::GiftFromManagement->value,
            self::MarketingCampaign->value,
            self::SystemErrorCredit->value,
        ];
    }

    public static function forDebit(): array
    {
        return [
            self::Return->value,
            self::SystemErrorDebit->value,
            self::RulesViolation->value,
            self::BalanceAdjustment->value,
            self::Other->value,
        ];
    }
}
