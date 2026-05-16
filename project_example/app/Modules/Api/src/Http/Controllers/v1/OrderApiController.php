<?php

namespace Api\Http\Controllers\v1;


use Api\Http\Controllers\BaseController;
use App\Modules\Order\src\Enums\OrderStatusEnum;
use App\Modules\Order\src\Http\Resources\OrderProfileApiDetailResource;
use App\Modules\Order\src\Http\Resources\OrderProfileDetailResource;
use Brand\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseField;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use Order\Models\Order;
use Product\Models\Product;
use User\Models\User;

class OrderApiController extends BaseController
{
// order
//    #[Endpoint("Данные API")]
//    #[ResponseField("id", "integer", "ID заказа.", required: true, example: 1)]
//    #[ResponseField("product_id", "integer|null", "ID продукта, если заказ оформлен на конкретный продукт.", required: false, example: null)]
//    #[ResponseField("combo_id", "integer|null", "ID комбо, если заказ оформлен на комбо.", required: false, example: null)]
//    #[ResponseField("transaction_method", "string", "Метод оплаты заказа.", required: true, example: "Оплата наличными при получении")]
//    #[ResponseField("delivery_type", "string", "Тип доставки заказа.", required: true, example: "Самовывоз")]
//    #[ResponseField("code", "string|null", "Код заказа, если есть.", required: false, example: null)]
//    #[ResponseField("promocode", "string|null", "Использованный промокод, если есть.", required: false, example: null)]
//    #[ResponseField("comment", "string|null", "Комментарий к заказу, если есть.", required: false, example: null)]
//    #[ResponseField("status", "string", "Статус заказа.", required: true, example: "Доставлен")]
//    #[ResponseField("order_price", "string", "Общая стоимость заказа в рублях.", required: true, example: "44347")]
//    #[ResponseField("order_weight", "string", "Общий вес заказа с учётом количества товаров.", required: true, example: "54.96")]
//    #[ResponseField("order_item_count", "integer", "Общее количество товаров в заказе с учётом quantity.", required: true, example: 14)]
//    #[ResponseField("created_at", "string", "Дата создания заказа.", required: true, example: "04.09.2025")]
//
//
//    #[ResponseField("user_fio", "string", "ФИО получателя.", required: true, example: "Фамилия11 Имя72")]
//    #[ResponseField("email", "string", "Email получателя.", required: true, example: "user789@test.com")]
//    #[ResponseField("phone", "string", "Телефон получателя.", required: true, example: "+79993919246")]
//
//    #[ResponseField("city", "string|null", "Город доставки (только для курьерской доставки).", required: false, example: "Москва")]
//    #[ResponseField("address_street", "string|null", "Улица доставки (только для курьерской доставки).", required: false, example: "Ленина")]
//    #[ResponseField("address_house", "string|null", "Номер дома (только для курьерской доставки).", required: false, example: "10")]
//
//    #[ResponseField("pickup_title", "string|null", "Название пункта выдачи (только для самовывоза).", required: false, example: "Пункт выдачи на Ленина, 10")]
//    #[ResponseField("pickup_phone", "string|null", "Телефон пункта выдачи.", required: false, example: "+79991234567")]
//    #[ResponseField("pickup_city", "string|null", "Город пункта выдачи.", required: false, example: "Москва")]
//
//    #[ResponseField("items", "array", "Список товаров и комбо в заказе.", required: true)]
//    #[ResponseField("items[].id", "integer", "ID позиции заказа.", required: true, example: 7)]
//    #[ResponseField("items[].type", "string", "Тип позиции: Product или Combo.", required: true, example: "Combo")]
//    #[ResponseField("items[].price", "string", "Полная цена позиции (price_for_unit * quantity).", required: true, example: "5500")]
//    #[ResponseField("items[].price_for_unit", "string", "Цена за единицу товара.", required: true, example: "2750")]
//    #[ResponseField("items[].quantity", "integer", "Количество единиц данной позиции.", required: true, example: 2)]
//    #[ResponseField("items[].weight", "string", "Вес одной единицы позиции.", required: true, example: "2.6")]
//    #[ResponseField("items[].weight_type", "string", "Тип измерения веса.", required: true, example: "кг")]
    #[Group('Заказы')]
    #[Endpoint("Данные API")]
    #[ResponseField("id", "integer", "ID заказа.", required: true, example: 1)]
    #[ResponseField("product_id", "integer|null", "ID продукта, если заказ оформлен на конкретный продукт.", required: false, example: null)]
    #[ResponseField("combo_id", "integer|null", "ID комбо, если заказ оформлен на комбо.", required: false, example: null)]
    #[ResponseField("transaction_method", "string", "Метод оплаты заказа.", required: true, example: "Оплата наличными при получении")]
    #[ResponseField("transaction_method_description", "string|null", "Описание метода оплаты.", required: false, example: "Оплата производится наличными курьеру при получении заказа.")]
    #[ResponseField("delivery_type", "string", "Тип доставки заказа.", required: true, example: "Самовывоз")]
    #[ResponseField("promocode", "string|null", "Использованный промокод, если есть.", required: false, example: "TEST")]
    #[ResponseField("promocode_percent", "string|null", "Процент скидки по промокоду.", required: false, example: 10)]
    #[ResponseField("comment", "string|null", "Комментарий к заказу, если есть.", required: false, example: "Позвонить за час до доставки")]
    #[ResponseField("status", "string", "Статус заказа.", required: true, example: "Доставлен")]
    #[ResponseField("order_price", "string", "Итоговая стоимость заказа (с учётом скидок и доставки) в рублях.", required: true, example: "44347")]
    #[ResponseField("order_weight", "string", "Общий вес заказа в кг.", required: true, example: "54.96")]
    #[ResponseField("order_item_count", "integer", "Общее количество товаров в заказе с учётом quantity.", required: true, example: 14)]
    #[ResponseField("created_at", "string", "Дата создания заказа.", required: true, example: "04.09.2025")]

    #[ResponseField("items[].price_without_sale", "integer|null", "Полная цена позиции без учёта скидки (только если скидка применена).", required: false, example: 4850)]
    #[ResponseField("items[].price_for_unit_without_sale", "string|null", "Цена за единицу без скидки.", required: false, example: "4850")]
    #[ResponseField("items[].unit_sale_percent", "integer", "Процент скидки на единицу товара.", required: true, example: 10)]
    #[ResponseField("items[].weight_for_unit", "string", "Вес одной единицы товара.", required: true, example: "4.17")]

// Финансы
    #[ResponseField("cart_sum", "string", "Сумма товаров до применения скидок и доставки.", required: true, example: "42000")]
    #[ResponseField("delivery_price", "string", "Стоимость доставки.", required: true, example: "500")]
    #[ResponseField("personal_discount", "string", "Сумма персональной скидки в рублях.", required: true, example: "1200")]
    #[ResponseField("promocode_discount", "string", "Сумма скидки по промокоду в рублях.", required: true, example: "800")]

// Дата и время доставки
    #[ResponseField("delivery_date", "string|null", "Желаемая дата доставки.", required: false, example: "10.09.2025")]
    #[ResponseField("delivery_time_from", "string|null", "Начало временного интервала доставки (в формате ЧЧ:ММ).", required: false, example: "10:00")]
    #[ResponseField("delivery_time_to", "string|null", "Конец временного интервала доставки (в формате ЧЧ:ММ).", required: false, example: "18:00")]

// Адрес (курьер)
    #[ResponseField("city", "string|null", "Город доставки (только для курьерской доставки).", required: false, example: "Москва")]
    #[ResponseField("address_street", "string|null", "Улица доставки (только для курьерской доставки).", required: false, example: "Ленина")]
    #[ResponseField("address_house", "string|null", "Номер дома (только для курьерской доставки).", required: false, example: "10")]

// Пункт выдачи (самовывоз)
    #[ResponseField("pickup_title", "string|null", "Название пункта выдачи (только для самовывоза).", required: false, example: "Пункт выдачи Центральный")]
    #[ResponseField("pickup_phone", "string|null", "Телефон пункта выдачи.", required: false, example: "+79991234567")]
    #[ResponseField("pickup_city", "string|null", "Город пункта выдачи.", required: false, example: "Москва")]


// Получатель
    #[ResponseField("user_fio", "string", "ФИО получателя.", required: true, example: "Фамилия11 Имя72")]
    #[ResponseField("email", "string", "Email получателя.", required: true, example: "user789@test.com")]
    #[ResponseField("phone", "string", "Телефон получателя.", required: true, example: "+79993919246")]

// Товары
    #[ResponseField("items", "array", "Список товаров и комбо в заказе.", required: true)]
    #[ResponseField("items[].id", "integer", "ID позиции заказа.", required: true, example: 7)]
    #[ResponseField("items[].type", "string", "Тип позиции: Product или Combo.", required: true, example: "Combo")]
    #[ResponseField("items[].price", "string", "Полная цена позиции (price_for_unit * quantity).", required: true, example: "5500")]
    #[ResponseField("items[].price_for_unit", "string", "Цена за единицу товара.", required: true, example: "2750")]
    #[ResponseField("items[].quantity", "integer", "Количество единиц данной позиции.", required: true, example: 2)]
    #[ResponseField("items[].weight", "string", "Общий вес позиции (weight_for_unit * quantity).", required: true, example: "5.2")]
    #[ResponseField("items[].weight_type", "string", "Тип измерения веса.", required: true, example: "кг")]
    #[ResponseField("items[].price_without_sale", "integer|null", "Полная цена позиции без скидки (только если скидка применена).", required: false, example: 4850)]
    #[ResponseField("items[].price_for_unit_without_sale", "string|null", "Цена за единицу без скидки.", required: false, example: "4850")]
    #[ResponseField("items[].unit_sale_percent", "integer", "Процент скидки на единицу.", required: true, example: 10)]
    #[ResponseField("items[].weight_for_unit", "string", "Вес одной единицы товара.", required: true, example: "4.17")]
//    #[ResponseFromApiResource(OrderProfileApiDetailResource::class, Order::class, collection: true, paginate: false)]
    #[ResponseFromFile('app/Modules/Api/src/Http/Responses/OrderApiController.index.json')]
    public function index(){
//        $orders = Order::where('id','<','3')->get();
//        return OrderProfileApiDetailResource::make($orders);

        $orders = Order::with([
            'deliveryAddress.city',
            'items',
            'pickupLocation.city_relation',
            'transactionMethod',
            'orderPromocode'
        ])
            ->where('id', '<', '3')
            ->get();

        return OrderProfileApiDetailResource::collection($orders);
    }
    #[Group('1С')]
    #[Endpoint('Обмен данными о заказе', 'Обрабатывает массив заказов')]
    #[Authenticated]
    #[BodyParam('orders', 'array','Массив заказов',required: true)]
    #[BodyParam('orders.bitrix_deal_id','string','ID сделки в Bitrix24',example:'123456',required: true)]
    #[BodyParam('orders.uuid','string','Уникальный идентификатор заказа в 1С',example:'uuid-123',required: false)]
    #[BodyParam('orders.price','float','Финальная стоимость заказа', example:'343414',required: false)]
    #[BodyParam('orders.payment_link','string','URL: Ссылка на оплату ',required: false)]
    #[BodyParam(name: 'orders.status', enum: OrderStatusEnum::class, description: 'Статус заказа', required: false)]
    #[ResponseField( 'status', 'string', 'Статус ответа', 'ok',)]
    #[ResponseField( 'data', 'array', 'Список заказов после обработки',)]
    public function exchange(Request $request) {

        $data = $request->validate([
            "orders" => ['required', 'array'],
            "orders.*.bitrix_deal_id" => ['nullable'],
            "orders.*.uuid" => ['nullable'],
            "orders.*.price" => ['nullable'],
            "orders.*.status" => ['nullable'],
            "orders.*.payment_link" => ['nullable'],
        ]);

        $legacyStatusMap = [
            'pending' => OrderStatusEnum::InProcessing->value,
            'approved' => OrderStatusEnum::Shipped->value,
            'delivered' => OrderStatusEnum::Completed->value,
        ];

        $allowedStatuses = array_map(static fn (OrderStatusEnum $case) => $case->value, OrderStatusEnum::cases());
        $acceptableStatuses = array_merge($allowedStatuses, array_keys($legacyStatusMap));

        $response = [];

        foreach ($data['orders'] as $item) {
            try {

                $validated = Validator::make($item, [
                     "bitrix_deal_id" => ['required', 'string', 'exists:orders,bitrix_deal_id'],
                     "uuid" => ['sometimes', 'nullable', 'string'],
                     "price" => ['sometimes', 'nullable', 'numeric'],
                     "status" => ['sometimes', 'nullable', Rule::in($acceptableStatuses)],
                     "payment_link" => ['sometimes', 'nullable', 'url'],
                ])->validated();

                // Ищем заказ по bitrix_deal_id (ID сделки в Bitrix24)
                $order = Order::where('bitrix_deal_id', $validated['bitrix_deal_id'])->first();

                // Проверяем, что заказ найден
                if (!$order) {
                    throw new \Exception("Заказ с bitrix_deal_id '{$item['bitrix_deal_id']}' не найден");
                }

                if(isset($validated['price'])) {
                    $order->price_final_1c = $validated['price'];
                }

                if(isset($item['price'])) {
                    $order->price_final_1c = $item['price'];
                }

                if(isset($validated['payment_link'])) {
                    $order->payment_link = $validated['payment_link'];
                }

                if(!empty($validated['status'])) {
                    $status = $validated['status'];
                    if (isset($legacyStatusMap[$status])) {
                        $status = $legacyStatusMap[$status];
                    }
                    $order->status = $status;
                }

                // Сохраняем uuid_1c, если передан
                if(!empty($validated['uuid'])) {
                    $order->uuid_1c = $validated['uuid'];
                }

                $order->save();
                
                $response[] = [
                    'bitrix_deal_id' => $order->bitrix_deal_id,
                    'site_id' => $order->id,
                    'uuid' => $order->uuid_1c,
                    'status'=> 'success',
                ];

            } catch (\Throwable $exception) {
                $response[] = [
                    'bitrix_deal_id' => $item['bitrix_deal_id'] ?? null,
                    'uuid' => $item['uuid'] ?? null,
                    'status' => 'error',
                    'error' => $exception->getMessage()
                ];
            }
        }

        return $this->sendResponse("success", $response);
    }
}
