<?php

namespace Product\Database\Seeders;

use Illuminate\Database\Seeder;
use Product\Models\Product;

class FillProductSeoTitleSeeder extends Seeder
{

    public function run(): void
    {
        $limit = 255;
        $ellipsis = '...';
        $maxLength = $limit - mb_strlen($ellipsis);
        $products = Product::whereNull('seo_title')
            ->orWhere('seo_title', '')
            ->get();

        $updated = 0;
        $skipped = 0;

        foreach ($products as $product) {
            if (!empty($product->seo_title)) {
                $skipped++;
                continue;
            }


            if (empty($product->name)) {
                $skipped++;
                continue;
            }

            $seoTitle = 'Купить ' . $product->name;

            if (mb_strlen($seoTitle) > $limit) {
                $seoTitle = mb_substr($seoTitle, 0, $maxLength) . $ellipsis;
            }

            $product->update(['seo_title' => $seoTitle]);
        }

    }
}

