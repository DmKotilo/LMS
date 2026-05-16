<?php

namespace Analitic\Database\Seeders;
use Analitic\Models\Analitic;
use Illuminate\Database\Seeder;
use Product\Models\Product;

class AnaliticSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();

        foreach ($products as $product) {
            Analitic::create([
                'product_id' => $product->id,
                'recomend_click' => rand(5, 100),
                'recomend_to_cart' => rand(0, 50),
                'created_at' => now()->subDays(rand(0, 6))->setTime(rand(8, 20), rand(0, 59)),
            ]);
        }
    }
}
