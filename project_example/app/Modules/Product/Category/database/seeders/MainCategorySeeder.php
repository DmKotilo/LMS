<?php

namespace Category\Database\Seeders;
use Category\Models\MainCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MainCategorySeeder extends Seeder
{
    public function run(): void
    {
        // удалить старые изображения
        Storage::disk('public')->deleteDirectory('MainCategory/desktop');
        Storage::disk('public')->deleteDirectory('MainCategory/mobile');

        $categories = [
            [
                'name' => 'Что приготовить?',
                'text_color' => '#ffffff',
                'category_id' => 1,
                'description' => null,
                'image_index' => 1,
                'desktop_font_size' => '32px',
                'mobile_font_size' => '20px',
                'desktop_line_height' => '38px',
                'mobile_line_height' => '24px',
            ],
            [
                'name' => 'Мясной мерч',
                'text_color' => '#02283F',
                'category_id' => 30,
                'description' => 'Подбери свой мерч для себя в разделе',
                'image_index' => 2,
                'desktop_font_size' => '24px',
                'mobile_font_size' => '20px',
                'desktop_line_height' => '28px',
                'mobile_line_height' => '24px',
            ],
            [
                'name' => 'Сыр La Petite Maison',
                'text_color' => '#02283F',
                'category_id' => 25,
                'description' => null,
                'image_index' => 3,
                'desktop_font_size' => '16px',
                'mobile_font_size' => '16px',
                'desktop_line_height' => '20px',
                'mobile_line_height' => '20px',
            ],
            [
                'name' => 'Wagyu (Вагю)',
                'text_color' => '#02283F',
                'category_id' => 6,
                'description' => null,
                'image_index' => 4,
                'desktop_font_size' => '16px',
                'mobile_font_size' => '16px',
                'desktop_line_height' => '20px',
                'mobile_line_height' => '20px',
            ],
            [
                'name' => 'Мраморная говядина',
                'text_color' => '#02283F',
                'category_id' => 15,
                'description' => null,
                'image_index' => 5,
                'desktop_font_size' => '24px',
                'mobile_font_size' => '16px',
                'desktop_line_height' => '28px',
                'mobile_line_height' => '20px',
            ],
            [
                'name' => 'Скидки недели',
                'text_color' => '#02283F',
                'category_id' => 7,
                'description' => null,
                'image_index' => 6,
                'desktop_font_size' => '16px',
                'mobile_font_size' => '16px',
                'desktop_line_height' => '20px',
                'mobile_line_height' => '20px',
            ],
            [
                'name' => 'Утка / Цыплята',
                'text_color' => '#ffffff',
                'category_id' => 26,
                'description' => null,
                'image_index' => 7,
                'desktop_font_size' => '16px',
                'mobile_font_size' => '16px',
                'desktop_line_height' => '20px',
                'mobile_line_height' => '20px',
            ],
            [
                'name' => 'Премиальные отрубы',
                'text_color' => '#ffffff',
                'category_id' => 8,
                'description' => null,
                'image_index' => 8,
                'desktop_font_size' => '24px',
                'mobile_font_size' => '20px',
                'desktop_line_height' => '28px',
                'mobile_line_height' => '24px',
            ],
            [
                'name' => 'Соусы и специи',
                'text_color' => '#02283F',
                'category_id' => 41,
                'description' => 'Подбери под свои предпочтения',
                'image_index' => 9,
                'desktop_font_size' => '24px',
                'mobile_font_size' => '16px',
                'desktop_line_height' => '28px',
                'mobile_line_height' => '20px',
            ],
        ];

        foreach ($categories as $position => $data) {
            $imageId = $data['image_index'];

            $desktopFiles = glob(public_path("images/Seeder/MainCategory/{$imageId}/image/desktop/{$imageId}*.png"));
            $desktopSourcePath = $desktopFiles[0] ?? null;
            $mobileFiles = glob(public_path("images/Seeder/MainCategory/{$imageId}/image/mobile/{$imageId}*.png"));
            $mobileSourcePath = $mobileFiles[0] ?? null;

            $filenameDesktop = Str::uuid() . '.png';
            $filenameMobile  = Str::uuid() . '.png';

            $desktopPath = "MainCategory/desktop/{$filenameDesktop}";
            $mobilePath  = "MainCategory/mobile/{$filenameMobile}";

            Storage::disk('public')->put($desktopPath, file_get_contents($desktopSourcePath));
            Storage::disk('public')->put($mobilePath, file_get_contents($mobileSourcePath));

            MainCategory::create([
                'name' => $data['name'],
                'category_id' => $data['category_id'],
                'description' => $data['description'],
                'text_color' => $data['text_color'],
                'desktop_font_size' => $data['desktop_font_size'],
                'mobile_font_size' => $data['mobile_font_size'],
                'desktop_line_height' => $data['desktop_line_height'],
                'mobile_line_height' => $data['mobile_line_height'],
                'desktop_photo' => $desktopPath,
                'mobile_photo' => $mobilePath,
                'is_active' => true,
                'position' => $position + 1,
            ]);
        }
    }

}
