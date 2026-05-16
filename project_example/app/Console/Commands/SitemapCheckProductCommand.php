<?php

namespace App\Console\Commands;

use App\Services\SitemapService;
use Illuminate\Console\Command;
use Product\Models\Product;

class SitemapCheckProductCommand extends Command
{
    protected $signature = 'sitemap:check-product {slug : Slug товара из URL /catalog/product/{slug}}';

    protected $description = 'Проверяет, почему товар не попадает в sitemap (условия и значения полей)';

    public function handle(SitemapService $sitemapService): int
    {
        $slug = $this->argument('slug');
        $product = Product::where('slug', $slug)->first();

        if (!$product) {
            $this->error("Товар с slug «{$slug}» не найден в БД.");
            return self::FAILURE;
        }

        $this->info("Товар id={$product->id}, slug={$product->slug}");
        $this->newLine();

        $categoriesCount = $product->categories()->count();
        $isActive = (bool) $product->is_active;
        $hasSlug = !empty($product->slug);
        $hasPrice = isset($product->price) && $product->price !== null;
        $notAnalog = $product->is_analog === false || $product->is_analog === null;
        $hasCategories = $categoriesCount > 0;

        $this->table(
            ['Проверка', 'Требуется', 'Факт', 'Результат'],
            [
                ['is_active', 'true', $product->is_active ? 'true' : 'false', $isActive ? 'OK' : 'FAIL'],
                ['slug', 'не пустой', $product->slug ?? 'NULL', $hasSlug ? 'OK' : 'FAIL'],
                ['price', 'не NULL', $product->price !== null ? (string) $product->price : 'NULL', $hasPrice ? 'OK' : 'FAIL'],
                ['is_analog', 'false или NULL', $product->is_analog === null ? 'NULL' : ($product->is_analog ? 'true' : 'false'), $notAnalog ? 'OK' : 'FAIL'],
                ['categories', '≥ 1', (string) $categoriesCount, $hasCategories ? 'OK' : 'FAIL'],
            ]
        );

        $allOk = $isActive && $hasSlug && $hasPrice && $notAnalog && $hasCategories;
        if ($allOk) {
            $this->info('Товар удовлетворяет всем условиям и должен быть в sitemap.');
            $included = $sitemapService->shouldIncludeModel('product', $product);
            $this->info('shouldIncludeModel(product): ' . ($included ? 'true' : 'false'));
        } else {
            $this->warn('Товар НЕ попадает в sitemap. Исправьте отмеченные FAIL выше.');
            if (!$hasCategories) {
                $this->line('  → Привяжите товар хотя бы к одной категории в админке.');
            }
        }

        return self::SUCCESS;
    }
}
