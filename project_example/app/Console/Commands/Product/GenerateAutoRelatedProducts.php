<?php

namespace App\Console\Commands\Product;

use Illuminate\Console\Command;
use Product\Models\Product;
use Product\Services\AutoRelatedProductsService;

class GenerateAutoRelatedProducts extends Command
{
    protected $signature = 'products:auto-related {product_id? : ID товара для пересчёта}';

    protected $description = 'Пересчитать блок "С этим товаром покупают" на основе статистики заказов';

    public function __construct(
        protected AutoRelatedProductsService $service
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $productId = $this->argument('product_id');

        if ($productId) {
            $product = Product::find($productId);

            if (!$product) {
                $this->error('Товар не найден');
                return self::FAILURE;
            }

            $this->service->rebuildForProduct($product);
            $this->info("Обновлены рекомендации для товара #{$product->getKey()}");

            return self::SUCCESS;
        }

        $this->service->rebuild();
        $this->info('Обновлены рекомендации для товаров с достаточной статистикой');

        return self::SUCCESS;
    }
}

