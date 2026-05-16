<?php

namespace Filter\Database\Seeders;
use Attribute\Models\Attribute;
use Attribute\Models\ProductAttribute;
use Brand\Models\Brand;
use Filter\Models\SavedFilter;
use Filter\Models\SavedFilterAttribute;
use Illuminate\Database\Seeder;
use Tag\Models\Tag;

class FilterSeeder extends Seeder
{
    public function run()
    {

        $brandIds = Brand::pluck('id')->toArray();
        $allTagIds = Tag::pluck('id')->toArray();

        for ($i = 1; $i <= 5; $i++) {
            $brandId = ($i % 2 === 0 && count($brandIds) > 0) ? $brandIds[array_rand($brandIds)] : null;

            $filter = SavedFilter::create([
                'user_id' => 1,
                'price_from' => rand(1000, 3000),
                'price_to' => rand(4000, 7000),
                'brand_id' => $brandId,
            ]);

            $attributes = Attribute::inRandomOrder()->limit(rand(2, 3))->get();

            foreach ($attributes as $attribute) {
                $values = ProductAttribute::where('attribute_id', $attribute->id)
                    ->distinct()
                    ->pluck('value')
                    ->filter()
                    ->values();

                $value = $values->isNotEmpty() ? $values->random() : '';

                SavedFilterAttribute::create([
                    'saved_filter_id' => $filter->id,
                    'attribute_id' => $attribute->id,
                    'value' => $value,
                ]);
            }

            if (count($allTagIds) > 0) {
                $tagIds = collect($allTagIds)->random(rand(1, 3))->all();
                $filter->tags()->attach($tagIds);
            }
        }
    }
}
