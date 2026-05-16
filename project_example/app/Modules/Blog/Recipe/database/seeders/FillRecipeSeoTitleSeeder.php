<?php

namespace App\Modules\Blog\Recipe\database\seeders;

use Illuminate\Database\Seeder;
use Recipe\Models\Recipe;

class FillRecipeSeoTitleSeeder extends Seeder
{

    public function run(): void
    {
        $titleLimit = 255;
        $descriptionLimit = 500;
        $ellipsis = '...';
        $titleMaxLength = $titleLimit - mb_strlen($ellipsis);
        $descriptionMaxLength = $descriptionLimit - mb_strlen($ellipsis);
        
        $recipes = Recipe::where(function($query) {
                $query->whereNull('seo_title')
                    ->orWhere('seo_title', '');
            })
            ->orWhere(function($query) {
                $query->whereNull('seo_description')
                    ->orWhere('seo_description', '');
            })
            ->get();

        $updated = 0;
        $skipped = 0;

        foreach ($recipes as $recipe) {
            $needsUpdate = false;
            $updateData = [];

            if (empty($recipe->title)) {
                $skipped++;
                continue;
            }

            // Заполняем seo_title если пусто
            if (empty($recipe->seo_title)) {
                $seoTitle = $recipe->title . ' — пошаговый рецепт с фото';

                if (mb_strlen($seoTitle) > $titleLimit) {
                    $seoTitle = mb_substr($seoTitle, 0, $titleMaxLength) . $ellipsis;
                }

                $updateData['seo_title'] = $seoTitle;
                $needsUpdate = true;
            }

            // Заполняем seo_description если пусто
            if (empty($recipe->seo_description)) {
                $seoDescription = 'Как приготовить ' . $recipe->title . '? Пошаговый рецепт с мясом от PRIME FOODS';

                if (mb_strlen($seoDescription) > $descriptionLimit) {
                    $seoDescription = mb_substr($seoDescription, 0, $descriptionMaxLength) . $ellipsis;
                }

                $updateData['seo_description'] = $seoDescription;
                $needsUpdate = true;
            }

            if ($needsUpdate) {
                $recipe->update($updateData);
                $updated++;
            } else {
                $skipped++;
            }
        }

    }
}

