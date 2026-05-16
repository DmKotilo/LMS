<?php

namespace UserSetting\Database\Seeders;
use App\Modules\Cart\src\Enums\CartItemTypeEnum;
use App\Modules\Cart\src\Enums\CartStatusEnum;
use App\Modules\Cart\src\Models\CartItem;
use App\Modules\Product\Product\src\Enums\PriceTypeEnum;
use App\Modules\Product\Product\src\Enums\WeightTypeEnum;
use Cart\Models\UserCart;
use Combo\Models\Combo;
use File\Models\Files\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Product\Models\Product;

class UserSettingSeeder extends Seeder
{
    public function run(): void
    {

    }

}
