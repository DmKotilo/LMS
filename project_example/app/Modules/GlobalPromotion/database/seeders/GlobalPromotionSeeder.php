<?php

namespace GlobalPromotion\Database\Seeders;

use App\Modules\GlobalPromotion\src\Enums\GlobalPromotionTypeEnum;
use Carbon\Carbon;
use Combo\Models\Combo;
use GlobalPromotion\Models\GlobalPromotion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Product\Models\Product;

class GlobalPromotionSeeder extends Seeder
{
    private array $usedProductIds = [];
    private array $usedComboIds = [];

    public function run(): void
    {
        $now = Carbon::now();
        $this->usedProductIds = DB::table('global_promotion_product')->pluck('product_id')->all();
        $this->usedComboIds = DB::table('global_promotion_combo')->pluck('combo_id')->all();

        $globalPromotionsData = [
            [
                'title' => '5% кэшбек для всех заказов',
                'description' => 'Повышенный кэшбек 5% действует для всех пользователей в указанный период.',
                'starts_at' => $now->copy()->subDays(2),
                'ends_at' => $now->copy()->addDays(12),
                'bonus_points' => null,
                'min_order_sum_for_bonus' => null,
                'max_order_sum_for_bonus' => null,
                'cashback_percent' => 5,
                'min_order_sum_for_cashback' => 0,
                'type' => GlobalPromotionTypeEnum::Cashback->value,
                'is_active' => true,
            ],
            [
                'title' => '10% кэшбек на популярные товары',
                'description' => 'В акции участвуют отдельные товары — получайте повышенный кэшбек 10%.',
                'starts_at' => $now->copy()->subDays(1),
                'ends_at' => $now->copy()->addDays(12),
                'bonus_points' => null,
                'min_order_sum_for_bonus' => null,
                'max_order_sum_for_bonus' => null,
                'cashback_percent' => 10,
                'min_order_sum_for_cashback' => null,
                'type' => GlobalPromotionTypeEnum::ProductCashback->value,
                'is_active' => true,
                'attach_products' => 3,
            ],
            [
                'title' => '7% кэшбек на комбо-наборы',
                'description' => 'Получайте 7% кэшбек при покупке акционных комбо-наборов.',
                'starts_at' => $now->copy()->subDays(2),
                'ends_at' => $now->copy()->addDays(36),
                'bonus_points' => null,
                'min_order_sum_for_bonus' => null,
                'max_order_sum_for_bonus' => null,
                'cashback_percent' => 7,
                'min_order_sum_for_cashback' => null,
                'type' => GlobalPromotionTypeEnum::ProductCashback->value,
                'is_active' => true,
                'attach_combos' => 2,
            ],
            [
                'title' => '3000 бонусов при заказе от 15 000 ₽',
                'description' => 'Получите 3000 бонусных баллов при заказе на сумму от 15 000 ₽.',
                'starts_at' => $now->copy()->subDays(2),
                'ends_at' => $now->copy()->addDays(34),
                'bonus_points' => 3000,
                'min_order_sum_for_bonus' => 15000,
                'max_order_sum_for_bonus' => null,
                'cashback_percent' => null,
                'min_order_sum_for_cashback' => null,
                'type' => GlobalPromotionTypeEnum::Bonus->value,
                'is_active' => true,
            ],
            [
                'title' => '2000 бонусов за заказ от 10 000 ₽',
                'description' => 'Начисление 2000 бонусных баллов при сумме заказа от 10 000 до 15 000 ₽.',
                'starts_at' => $now->copy()->subDays(1),
                'ends_at' => $now->copy()->addDays(14),
                'bonus_points' => 2000,
                'min_order_sum_for_bonus' => 10000,
                'max_order_sum_for_bonus' => 15000,
                'cashback_percent' => null,
                'min_order_sum_for_cashback' => null,
                'type' => GlobalPromotionTypeEnum::Bonus->value,
                'is_active' => true,
            ],
        ];

        foreach ($globalPromotionsData as $data) {
            $productsToAttach = $data['attach_products'] ?? null;
            $combosToAttach = $data['attach_combos'] ?? null;
            unset($data['attach_products'], $data['attach_combos']);

            $promotion = GlobalPromotion::query()->create($data);
            if ($productsToAttach) {
                $this->attachProducts($promotion, $productsToAttach);
            }

            if ($combosToAttach) {
                $this->attachCombos($promotion, $combosToAttach);
            }
        }
    }

    private function attachProducts(GlobalPromotion $promotion, int $limit): void
    {
        $products = Product::query()
            ->whereNotIn('id', $this->usedProductIds)
            ->inRandomOrder()
            ->take($limit)
            ->pluck('id');
        if ($products->isEmpty()) {
            return;
        }

        $promotion->products()->attach($products);
        $this->usedProductIds = array_merge($this->usedProductIds, $products->all());
    }

    private function attachCombos(GlobalPromotion $promotion, int $limit): void
    {
        $combos = Combo::query()
            ->whereNotIn('id', $this->usedComboIds)
            ->inRandomOrder()
            ->take($limit)
            ->pluck('id');
        if ($combos->isEmpty()) {
            return;
        }

        $promotion->combos()->attach($combos);
        $this->usedComboIds = array_merge($this->usedComboIds, $combos->all());
    }

    private function nextTitle(string $base): string
    {
        $title = sprintf('%s #%02d — %s', self::TITLE_PREFIX, $this->titleCounter, $base);
        $this->titleCounter++;

        return $title;
    }
}
