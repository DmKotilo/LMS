<?php

namespace Cart\Database\Seeders;
use App\Modules\Cart\src\Enums\CartItemTypeEnum;
use App\Modules\Cart\src\Enums\CartStatusEnum;
use App\Modules\Cart\src\Models\CartItem;
use Cart\Models\UserCart;
use Combo\Models\Combo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Product\Models\Product;

class CartSeeder extends Seeder
{
    public function run(): void
    {
        $cart = UserCart::firstOrCreate(
            ['user_id' => 1, 'status' => CartStatusEnum::Active],
            ['session_id' => '123123123']
        );


        $products = Product::take(2)->get();

        foreach ($products as $product) {
            CartItem::create([
                'cart_id' => $cart->id,
                'item_type' => CartItemTypeEnum::Product->value,
                'item_id' => $product->id,
                'quantity' => rand(1, 5),
            ]);
        }

        $combos = Combo::take(2)->get();

        foreach ($combos as $combo) {
            CartItem::create([
                'cart_id' => $cart->id,
                'item_type' => CartItemTypeEnum::Combo->value,
                'item_id' => $combo->id,
                'quantity' => rand(1, 3),
            ]);
        }
    }

}
