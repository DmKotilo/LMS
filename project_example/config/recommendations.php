<?php

return [
    'auto_related' => [
        'period_months' => env('AUTO_RELATED_PERIOD_MONTHS', 3),// за какой срок брать заказы
        'min_product_orders' => env('AUTO_RELATED_MIN_PRODUCT_ORDERS', 5),//мин заказов с этим товаром
        'min_confidence' => env('AUTO_RELATED_MIN_CONFIDENCE', 0.3),//процент совместимости >30%
        'limit' => env('AUTO_RELATED_LIMIT', 10),
        'eligible_statuses' => [
            \App\Modules\Order\src\Enums\OrderStatusEnum::Paid->value,
            \App\Modules\Order\src\Enums\OrderStatusEnum::Confirmed->value,
            \App\Modules\Order\src\Enums\OrderStatusEnum::InWork->value,
            \App\Modules\Order\src\Enums\OrderStatusEnum::Shipped->value,
            \App\Modules\Order\src\Enums\OrderStatusEnum::ReadyForPickup->value,
            \App\Modules\Order\src\Enums\OrderStatusEnum::Completed->value,
        ],
    ],
];

