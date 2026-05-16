<?php

namespace Address\Http\Resources;

use Address\Model\Address;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Knuckles\Scribe\Attributes\ResponseField;

class AddressResource extends JsonResource
{


    #[ResponseField("data", "array")]
    #[ResponseField("data.value", "string")]
    #[ResponseField("data.region", "string")]
    #[ResponseField("data.added", "added", 'доп информация от пользователя, уточнения')]
    public static $model = Address::class;
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            'region' => $this->region,
            'city_id' => $this->city->name,
            'city_dadata' => $this->city_dadata,
            'street' => $this->street,
//            'house' => (int)$this->house,
            "entrance" =>  (int)$this->entrance,
            "floor" =>  (int)$this->floor,
            "apartment" =>  (int)$this->apartment,
//            "contact_method_id" =>  $this->contactMethod?->name,
            "is_primary" =>  $this->is_primary,
            "car_pass" =>  $this->car_pass,
            "final_address" =>  $this->final_address,
            "value_dadata" =>  $this->value_dadata,
            "dadata_json" =>  $this->dadata_json,
            'delivery_zone' => [
                'id' => $this->deliveryZone?->id,
                'name' => $this->deliveryZone?->name,
            ],
        ];
    }
}
