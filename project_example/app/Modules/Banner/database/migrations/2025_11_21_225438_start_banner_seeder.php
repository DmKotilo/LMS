<?php

use App\Modules\Banner\src\Http\Enums\BannerLocationEnum;
use App\Modules\Banner\src\Http\Enums\BannerTypeEnum;
use Banner\Models\Banner;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        
        $cartPageBannersCount = Banner::where('location', BannerLocationEnum::CartPage)
            ->whereIn('banner_type', [
                BannerTypeEnum::PromotionBanner,
                BannerTypeEnum::FirstOrderGift
            ])
            ->count();

        $profilePageBannersCount = Banner::where('location', BannerLocationEnum::ProfilePage)
            ->where('banner_type', BannerTypeEnum::PromotionBanner)
            ->count();

        $privilegePageBannersCount = Banner::where('location', BannerLocationEnum::PrivilegePage)
            ->where('banner_type', BannerTypeEnum::PromotionBanner)
            ->count();

        $makingOrderPageBannersCount = Banner::where('location', BannerLocationEnum::MakingOrderPage)
            ->where('banner_type', BannerTypeEnum::PromotionBanner)
            ->count();

        if ($cartPageBannersCount < 2 
            || $profilePageBannersCount < 2 
            || $privilegePageBannersCount < 2 
            || $makingOrderPageBannersCount < 2) {
            Artisan::call('db:seed', [
                '--class' => 'Banner\Database\Seeders\BannerSeeder'
            ]);
        }
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
