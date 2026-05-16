<?php

namespace App\Modules\Order\src\Http\Resources;

use App\Modules\Cart\src\Traits\GetCart;
use Combo\Models\Combo;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class ComboOrderResource extends JsonResource
{
    use GetCart;
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */

    public static $model = Combo::class;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'article' => $this->article_number,
            'name' => html_entity_decode($this->translate('name'), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'weight_type' => $this->weight_type?->toString(),
            'price' => $this->total_price,
            'sale_price' => $this->sale_price,
            'sale_percent' => $this->getSalePercent(),
            'price_type' => $this->price_type?->toString(),
            'images' => FileResource::collection($this->images),
        ];
    }



}
