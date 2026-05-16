<?php

namespace Delivery\Http\Resources;


use Delivery\Models\DeliveryZone;
use Delivery\Models\PickupLocation;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class DeliveryZoneResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = DeliveryZone::class;
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'min_order_sum' => $this->min_order_sum,
            'delivery_price' => $this->delivery_price,
            'description' => Str::of($this->description)->stripTags(),
            'time_intervals' => $this->time_intervals,
            'delivery_time' => $this->delivery_time,
        ];
    }
}
