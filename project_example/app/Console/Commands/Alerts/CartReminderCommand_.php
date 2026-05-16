<?php

namespace App\Console\Commands\Alerts;

use Alert\Enums\AlertTypeEnum;
use Alert\Models\Alert;
use Carbon\Carbon;
use Illuminate\Console\Command;
use User\Models\User;

class CartReminderCommand_ extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:cart-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Напоминание о незавершённых покупках (брошенная корзина)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $users = User::whereHas('cart.items')->get();

        foreach ($users as $user) {
            $abandoned = false;

            foreach ($user->cart->items as $item) {
                // если последний товар был добавлен более 30 дней назад
                if ($item->created_at->lt($now->subDays(30))) {
                    $abandoned = true;
                    break;
                }
            }

            if ($abandoned) {
                Alert::create([
                    'user_id' => $user->id,
                    'type' => AlertTypeEnum::CartReminder,
                    'title' => __('alerts.cartReminderTitle'),
                    'message' => __('alerts.cartReminderMessage'),
                    'button_text' => __('alerts.buttonGoToCart'),
                    'button_link' => route('cart.index'),
                ]);
            }
        }
    }
}
