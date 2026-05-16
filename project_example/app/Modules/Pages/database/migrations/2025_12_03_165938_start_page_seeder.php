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

            Artisan::call('db:seed', [
                '--class' => 'Pages\Database\Seeders\PageSeeder'
            ]);

    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
