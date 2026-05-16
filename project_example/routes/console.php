<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// запуск команды отправки уведомлений о скором сгорании бонусов каждый день в 11:00
Schedule::command('notifications:points-expire')->dailyAt('11:00');

// запуск команды отправки уведомлений о брошенной корзине каждый день в 10:00
//Schedule::command('notifications:cart-reminder')->dailyAt('10:00');
Schedule::command('notifications:cart-reminder')->hourly();


// запуск команды для обновления статусов промоакций каждый день в полночь
Schedule::command('promotions:deactivate-promotions')->daily();


// запуск команды истечения бонусов каждый день в полночь
Schedule::command('bonuses:expire')->hourly();

// Создание алертов о начислении бонусов за заказы после активации (active_date <= now)
Schedule::command('notifications:purchase-bonus-activate')->hourly();

// Создание алертов о бонусах за подписку после активации начислений
Schedule::command('notifications:subscription-bonus-activate')->hourly();

// Обновление уровней лояльности всех пользователей
Schedule::command('loyalty:update-levels')->dailyAt('03:00');

// Перегенерация авто-рекомендаций по статистике заказов
Schedule::command('products:auto-related')->dailyAt('03:00');

// Начисление бонусов за 2 дня до дня рождения
Schedule::command('bonuses:birthday')->dailyAt('23:40');

// Удаление баннеров при истечении акций
Schedule::command('banners:delete-expired')->daily();

// Перегенерация sitemap раз в сутки (в 04:00)
Schedule::command('sitemap:generate')->dailyAt('04:00');

// Динамическое планирование для Яндекс фидов
try {
    $feeds = \YandexFeed\Models\YandexFeed::where('is_active', true)
        ->where('cron_auto_install', true)
        ->where(function($query) {
            $query->whereNotNull('cron_interval_hours')
                ->orWhereNotNull('cron_start_time');
        })
        ->get();

    foreach ($feeds as $feed) {
        $command = Schedule::command("yandex-feed:generate {$feed->id}");

        if ($feed->cron_start_time) {
            $time = \Carbon\Carbon::parse($feed->cron_start_time);
            $command->dailyAt($time->format('H:i'));
        }
        elseif ($feed->cron_interval_hours && $feed->cron_interval_hours > 0) {
            if ($feed->cron_interval_hours == 1) {
                $command->hourly();
            } else {
                $command->cron("0 */{$feed->cron_interval_hours} * * *");
            }
        }
    }
} catch (\Exception $e) {
}
