<?php

namespace App\Modules\Promotion\src\Http\Resources;


use App\Modules\Promotion\src\Enums\PromotionTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class PromotionTypeResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public function toArray(Request $request): array
    {
        return collect(PromotionTypeEnum::cases())
            ->map(function ($case) {
                return [
                    'name' => $case->toString(),
                    'type' => $case->value,
                ];
            })
            ->toArray();
    }

}
