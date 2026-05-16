<?php

namespace App\Modules\Combo\src\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComboProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => html_entity_decode($this->translate('name'), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'price' => $this->pivot->price ?? $this->total_price,
            'price_type' => $this->price_type?->toString(),
            'sale_price' => $this->sale_price,
            'sale_percent' => $this->getSalePercent(),
            'images' => $this->getDisplayImages(),
        ];
    }
}

