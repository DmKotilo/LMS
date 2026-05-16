<?php

namespace App\Modules\Blog\Article\database\seeders;

use Article\Models\Article;
use Illuminate\Database\Seeder;

class FillArticleSeoTitleSeeder extends Seeder
{

    public function run(): void
    {
        $titleLimit = 255;
        $descriptionLimit = 160;
        $ellipsis = '...';
        $titleMaxLength = $titleLimit - mb_strlen($ellipsis);
        
        $suffix = '. Узнайте первыми о новинках, акциях и событиях PRIME FOODS.';
        $suffixLength = mb_strlen($suffix);
        $descriptionMaxLength = $descriptionLimit - $suffixLength;
        
        $articles = Article::where(function($query) {
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

        foreach ($articles as $article) {
            $needsUpdate = false;
            $updateData = [];

            if (empty($article->title)) {
                $skipped++;
                continue;
            }

            // Заполняем seo_title если пусто
            if (empty($article->seo_title)) {
                $seoTitle = $article->title . ' — PRIME FOODS';

                if (mb_strlen($seoTitle) > $titleLimit) {
                    $seoTitle = mb_substr($seoTitle, 0, $titleMaxLength) . $ellipsis;
                }

                $updateData['seo_title'] = $seoTitle;
                $needsUpdate = true;
            }

            // Заполняем seo_description если пусто
            if (empty($article->seo_description)) {
                $miniDescription = $article->mini_description ?? '';
                
                // Обрезаем mini_description до нужной длины
                if (mb_strlen($miniDescription) > $descriptionMaxLength) {
                    $miniDescription = mb_substr($miniDescription, 0, $descriptionMaxLength) . $ellipsis;
                }
                
                $seoDescription = $miniDescription . $suffix;

                $updateData['seo_description'] = $seoDescription;
                $needsUpdate = true;
            }

            if ($needsUpdate) {
                $article->update($updateData);
                $updated++;
            } else {
                $skipped++;
            }
        }

    }
}

