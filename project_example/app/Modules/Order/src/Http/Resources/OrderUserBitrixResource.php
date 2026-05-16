<?php

namespace App\Modules\Order\src\Http\Resources;


use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use User\Models\User;

class OrderUserBitrixResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = User::class;

    public function toArray(Request $request): array
    {
        return [
            'NAME' => $this->name,
            'LAST_NAME' => $this->last_name,
            'SECOND_NAME' => $this->second_name,
            'BIRTHDATE' => $this->birth_date?->format(\DateTime::ATOM),
            'PHONE' => "+7".$this->phone,
            'EMAIL' => $this->email,
            "TYPE_ID" => "CLIENT",
            "SOURCE_ID" => "WEB",
        ];
    }

}
