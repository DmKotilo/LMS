<?php

namespace App\Console\Commands\Alerts;

use Alert\Enums\AlertTypeEnum;
use Alert\Models\Alert;
use App\Notifications\CartReminderNotification; // <--- Добавь импорт
use Cart\Models\UserCart;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail; // Можно удалить, если не используется
use User\Models\User;

class CartReminderCommand extends Command
{
    protected $signature = 'notifications:cart-reminder {--days=0.04 : Количество дней бездействия}';

    protected $description = 'Напоминание о незавершённых покупках (брошенная корзина)';

    public function handle()
    {
        $days = $this->option('days');
        $cutoff = Carbon::now()->subDays($days);

        $carts = UserCart::with(['user', 'items.item'])
            ->whereHas('items') // корзина не пуста
            ->where('updated_at', '<', $cutoff)
            ->whereNotNull('user_id') // только с пользователем
            ->whereHas('user', function ($query) {
                $query->whereHas('setting', function ($settingsQuery) {
                    $settingsQuery->where('email_notifications', true);
                });
            })
            ->get();

        foreach ($carts as $cart) {
            $user = $cart->user;

            // Отправляем уведомление (например, в базу, как в текущем коде)
            Alert::create([
                'user_id' => $user->id,
                'type' => AlertTypeEnum::CartReminder,
                'title' => __('alerts.cartReminderTitle'),
                'message' => __('alerts.cartReminderMessage'),
                'button_text' => __('alerts.buttonGoToCart'),
                'button_link' => route('cart.index'),
            ]);

            // Отправляем email через Notification
            $user->notify(new CartReminderNotification($cart));
        }

        $this->info('Напоминания отправлены для ' . $carts->count() . ' корзин.');
    }
}
