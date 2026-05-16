<?php

namespace App\Modules\Order\src\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Order\Models\Order;

class OrderBitrixResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = Order::class;

    public function toArray(Request $request): array
    {
        $transactionMethod = match ($this->transactionMethod?->name) {
            'Оплата наличными при получении' => 325,
            'Оплата банковской картой' => 331,
            default => null
        };
        return [
            "Comments" => $this->comment,
            "UF_CRM_1591104639" => $transactionMethod, //форма оплаты
            "UF_CRM_1591112041002" => $this->deliveryAddress?->final_address, //адрес доставки
            "UF_CRM_1593851565" => $this->delivery_time_from, //Время доставки C
            "UF_CRM_1593851582" => $this->delivery_time_to, //Время доставки По
        ];
    }

}
