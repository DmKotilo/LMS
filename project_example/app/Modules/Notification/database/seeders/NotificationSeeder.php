<?php

namespace Notification\Database\Seeders;
use Alert\Enums\AlertTypeEnum;
use Alert\Models\Alert;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Notification\Enums\NotificationTypeEnum;
use Notification\Models\PromoNotification;
use User\Models\User;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $notifications = [
            [
                'user_id' => 1,
                'promotion_id' => 1,
                'product_id' => null,
                'type' => NotificationTypeEnum::Promo,
                'is_read' => false,
                'user_deleted' => false,
            ],
            [
                'user_id' => 1,
                'promotion_id' => 2,
                'product_id' => null,
                'type' => NotificationTypeEnum::Promo,
                'is_read' => true,
                'user_deleted' => false,
            ],
            [
                'user_id' => 1,
                'promotion_id' => null,
                'product_id' => 3,
                'type' => NotificationTypeEnum::Novelty,
                'is_read' => false,
                'user_deleted' => false,
            ],


            [
                'user_id' => 2,
                'promotion_id' => 1,
                'product_id' => null,
                'type' => NotificationTypeEnum::Promo,
                'is_read' => false,
                'user_deleted' => false,
            ],
            [
                'user_id' => 2,
                'promotion_id' => null,
                'product_id' => 5,
                'type' => NotificationTypeEnum::Novelty,
                'is_read' => false,
                'user_deleted' => false,
            ],
            [
                'user_id' => 2,
                'promotion_id' => 3,
                'product_id' => null,
                'type' => NotificationTypeEnum::Promo,
                'is_read' => true,
                'user_deleted' => false,
            ],
        ];

        foreach ($notifications as $data) {
            PromoNotification::create($data);
        }
    }
}
