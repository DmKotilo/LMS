<?php

namespace Product\Database\Seeders;
use App\Modules\Product\Product\src\Enums\PageSlugEnum;
use App\Modules\Product\Product\src\Enums\PriceTypeEnum;
use App\Modules\Product\Product\src\Enums\WeightTypeEnum;
use App\Modules\Product\Product\src\Models\RecomendedProduct;
use App\Modules\Product\Product\src\Models\RecomendedProductPage;
use Attribute\Enums\InputTypeEnum;
use Attribute\Models\Attribute;
use Brand\Models\Brand;
use Illuminate\Support\Facades\File as FileFacade;
use Category\Models\Category;
use File\Models\Files\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Localization\Models\Lang;
use Localization\Models\Localization;
use Product\Models\CuttingProduct;
use Product\Models\Product;
use Tag\Models\Tag;

class RecomendedProductsSeeder extends Seeder
{

    public function run()
    {

        RecomendedProduct::truncate();
        RecomendedProductPage::truncate();


        $pages = [];
        foreach (PageSlugEnum::cases() as $page) {
            $pages[$page->value] = RecomendedProductPage::create([
                'slug' => $page->value,
                'name' => $page->toString(),
            ]);
        }

        foreach ($pages as $slug => $page) {
            $products = Product::inRandomOrder()->limit(10)->get();

            foreach ($products as $product) {
                RecomendedProduct::create([
                    'product_id' => $product->id,
                    'recomended_product_page_id' => $page->id,
                ]);
            }
        }
    }

}
