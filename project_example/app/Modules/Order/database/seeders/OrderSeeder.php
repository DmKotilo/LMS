<?php

namespace Order\Database\Seeders;
use Address\Model\Address;
use App\Modules\Order\src\Enums\OrderStatusEnum;
use Combo\Models\Combo;
use Illuminate\Database\Seeder;
use Order\Enums\OrderDeliveryTypeEnum;
use Order\Enums\OrderPromocodeStatusEnum;
use Order\Models\Order;
use Order\Models\OrderItem;
use Order\Models\OrderProduct;
use Order\Models\OrderPromocode;
use Product\Models\Product;
use User\Models\User;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $userIds    = User::pluck('id')->toArray();
        $productIds = Product::pluck('id')->toArray();
        $comboIds   = Combo::pluck('id')->toArray();
        $statuses   = array_map(fn($case) => $case->value, OrderStatusEnum::cases());

        $promocodes = OrderPromocode::where('status', OrderPromocodeStatusEnum::active->value)->get();

        for ($i = 0; $i < 40; $i++) {
            $userId = $userIds[array_rand($userIds)];
            $address = Address::where('user_id', $userId)->inRandomOrder()->first();

            if (!$address) {
                continue;
            }

            $order = Order::create([
                'user_id' => $userId,
                'transaction_method_id' => rand(1, 2),
                'delivery_address_id' => $address->id,
                'pickup_location_id' => null,
                'name' => 'Имя' . rand(1, 99),
                'last_name' => 'Фамилия' . rand(1, 99),
                'second_name' => null,
                'delivery_date' => now()->addDays(rand(0, 5)),
                'delivery_time_from' => '10:00',
                'delivery_time_to' => '12:00',
                'promo' => null,
                'email' => 'user' . rand(100, 999) . '@test.com',
                'phone' => '+7999' . rand(1000000, 9999999),
                'comment' => null,
                'cart_sum' => 0,
                'delivery_price' => rand(0, 1) ? 0 : 199,
                'weight' => 0,
                'quantity' => 0,
                'personal_discount' => 0,
                'promocode_discount' => 0,
                'price_final' => 0,
                'bonus_spent' => 0,
                'delivery_type' => OrderDeliveryTypeEnum::pickup->value,
                'status' => $statuses[array_rand($statuses)],
            ]);

            $totalWeight   = 0;
            $totalPrice    = 0;
            $totalQuantity = 0;

            $itemsInOrder = rand(6, 8);

            for ($j = 0; $j < $itemsInOrder; $j++) {
                $item = rand(0,1) === 0 && !empty($productIds)
                    ? Product::find($productIds[array_rand($productIds)])
                    : Combo::find($comboIds[array_rand($comboIds)]);

                if (!$item) continue;

                $quantity = rand(1,3);
                $priceForUnit = $item->getCartPrice();
                $weightForUnit = $item->weight ?? 0;

                // случайная скидка на товар
                $unitSalePercent = rand(0, 20);
                $priceForUnitWithoutSale = $priceForUnit;
                $priceForUnit = round($priceForUnit * (1 - $unitSalePercent / 100), 2);

                $linePrice  = $priceForUnit * $quantity;
                $lineWeight = $weightForUnit * $quantity;

                OrderItem::create([
                    'order_id' => $order->id,
                    'item_type' => get_class($item),
                    'item_id' => $item->id,
                    'price' => $linePrice,
                    'price_for_unit' => $priceForUnit,
                    'price_for_unit_without_sale' => $priceForUnitWithoutSale,
                    'unit_sale_percent' => $unitSalePercent,
                    'weight' => $lineWeight,
                    'weight_for_unit' => $weightForUnit,
                    'weight_type' => $item->weight_type ?? null,
                    'quantity' => $quantity,
                ]);

                $totalWeight   += $lineWeight;
                $totalPrice    += $linePrice;
                $totalQuantity += $quantity;
            }

            // применяем промокод в 30% случая
            $promocodeDiscount = 0;
            $appliedPromocodeId = null;
            if ($promocodes->isNotEmpty() && rand(1, 100) <= 30) {
                $promo = $promocodes->random();
                $appliedPromocodeId = $promo->id;
                $promocodeDiscount = round($totalPrice * ($promo->percent / 100), 2);
            }

            $order->update([
                'cart_sum' => $totalPrice,
                'weight' => $totalWeight,
                'quantity' => $totalQuantity,
                'order_promocode_id' => $appliedPromocodeId,
                'promocode_discount' => $promocodeDiscount,
                'bonus_spent' => 0,
                'price_final' => $totalPrice - $promocodeDiscount + $order->delivery_price,
            ]);
        }
    }
}


