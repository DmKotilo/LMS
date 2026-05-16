<?php

namespace App\Modules\WhiteList\List\database\seeders;
use Illuminate\Database\Seeder;
use List\Models\UserWhitelist;
use List\Models\WhitelistItem;
use Product\Models\Product;
use Recipe\Models\Recipe;

class WhiteListSeeder extends Seeder
{
    public function run(): void
    {

        $whitelist = UserWhitelist::create([
            'user_id' => 1,
        ]);

        $products = Product::inRandomOrder()->limit(3)->pluck('id');
        $recipes = Recipe::inRandomOrder()->limit(2)->pluck('id');

        foreach ($products as $productId) {
            WhitelistItem::create([
                'whitelist_id' => $whitelist->id,
                'item_id' => $productId,
                'item_type' => Product::class,
            ]);
        }

        foreach ($recipes as $recipeId) {
            WhitelistItem::create([
                'whitelist_id' => $whitelist->id,
                'item_id' => $recipeId,
                'item_type' => Recipe::class,
            ]);
        }

    }
}
