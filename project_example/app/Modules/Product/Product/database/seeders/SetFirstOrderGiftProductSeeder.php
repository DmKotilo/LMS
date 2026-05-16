<?php

namespace Product\Database\Seeders;

use Illuminate\Database\Seeder;
use Product\Models\Product;

class SetFirstOrderGiftProductSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment('local', 'develop', 'dev')) {
            return;
        }

        $product = Product::find(100);
        if (!$product){
            return;
        }
        $product->update(['is_first_order_gift' => true]);

    }
}

