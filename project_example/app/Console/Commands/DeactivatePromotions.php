<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Promotion\Models\Promotion;

class DeactivatePromotions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'promotions:deactivate-promotions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'выключить акции, срок которых истёк';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $expiredPromotions = Promotion::where('is_active', true)
            ->whereDate('end_date', '<', $now)
            ->get();

        $count = 0;

        foreach ($expiredPromotions as $promotion) {
            $promotion->is_active = false;
            $promotion->save();

            $promotion->products()->detach();
            $promotion->combos()->detach();

            $count++;
        }

        $this->info("Выключено {$count} акций, продукты и комбо отвязаны.");
    }

}
