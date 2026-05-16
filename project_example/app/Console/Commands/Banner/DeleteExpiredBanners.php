<?php

namespace App\Console\Commands\Banner;

use Banner\Models\Banner;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteExpiredBanners extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'banners:delete-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Удаляет баннеры, привязанные к истекшим глобальным акциям';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        // Находим баннеры, привязанные к истекшим акциям
        $expiredBanners = Banner::whereNotNull('global_promotion_id')
            ->whereHas('globalPromotion', function ($query) use ($now) {
                $query->where('ends_at', '<', $now);
            })
            ->get();

        $count = $expiredBanners->count();

        foreach ($expiredBanners as $banner) {
            $banner->delete();
        }

        $this->info("Удалено {$count} баннеров, привязанных к истекшим акциям.");
    }
}

