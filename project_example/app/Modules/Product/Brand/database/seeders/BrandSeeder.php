<?php

namespace Brand\Database\Seeders;
use Brand\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run()
    {
        Brand::firstOrCreate([
            'name' => "TM Primebeef",
            'name_1c' => "TM Primebeef",
            'slug' => Str::slug("TM Primebeef"),
            'explanation' => "Объяснение для бренда TM Primebeef",
        ]);
        Brand::firstOrCreate([
            'name' => "TM Primefoods",
            'name_1c' => "TM Primefoods",
            'slug' => Str::slug("TM Primefoods"),
            'explanation' => "Объяснение для бренда TM Primefoods",
        ]);
    }
}
