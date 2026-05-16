<?php

namespace Tag\Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Tag\Models\Tag;

class TagSeeder extends Seeder
{
    public function run()
    {
        Tag::firstOrCreate([
            'name' => "Тонкий край",
            'slug' => Str::slug("Тонкий край"),
        ]);
        Tag::firstOrCreate([
            'name' => "Мякоть",
            'slug' => Str::slug("Мякоть"),
        ]);
        Tag::firstOrCreate([
            'name' => "Толстый край",
            'slug' => Str::slug("Толстый край"),
        ]);
        Tag::firstOrCreate([
            'name' => "Лопатка",
            'slug' => Str::slug("Лопатка"),
        ]);
        Tag::firstOrCreate([
            'name' => "Грудинка",
            'slug' => Str::slug("Грудинка"),
        ]);
        Tag::firstOrCreate([
            'name' => "Шея",
            'slug' => Str::slug("Шея"),
        ]);
    }
}
