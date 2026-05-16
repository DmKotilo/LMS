<?php

namespace App\Modules\Delivery\src\Http\Resources;


use Delivery\Models\PickupLocation;
use Illuminate\Http\Resources\Json\JsonResource;

class PickupLocationResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = PickupLocation::class;
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'phone' => $this->phone,
            'working_hours_from' => $this->working_hours_from,
            'working_hours_to' => $this->working_hours_to,
            'site' => $this->site,
            'delivery_time' => $this->delivery_time,
            'postal_code' => $this->postal_code,
            'region' => $this->region_dadata,
            'city' => $this->city_dadata,
            'street' => $this->street,
            'house' => $this->house,
            'entrance' => $this->entrance,
            'floor' => $this->floor,
            'apartment' => $this->apartment,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'fullAddress' => $this->full_address,
            'pickup_bonus_points' => $this->city_relation?->pickup_bonus_points ?? 0,
        ];
    }





}
