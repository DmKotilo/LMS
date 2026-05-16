<?php

namespace App\Modules\Promotion\src\Http\Resources;

use App\Modules\Combo\src\Http\Resources\ComboCardResource;
use Carbon\Carbon;
use Catalog\Http\Resources\ProductCardResource;
use Combo\Models\Combo;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Product\Models\Product;
use Promotion\Models\Promotion;

class PromotionDetailResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = Promotion::class;
    public function toArray(Request $request): array
    {
        $endDate = Carbon::parse($this->end_date)->endOfDay();
        $now = Carbon::now()->startOfDay();

        $items = collect()
           ->merge($this->products->map(fn($product) => [
                           'item_type' => Product::class,
                           'is_combo' => false,
                           'item' => new ProductCardResource($product),
                       ]))
                       ->merge($this->combos->map(fn($combo) => [
                           'item_type' => Combo::class,
                           'is_combo' => true,
                           'item' => new ComboCardResource($combo),
                       ]))
                       ->values();

        return [
            'id' => $this->id,
            'image' => new FileResource($this->image),
            'name' => $this->translate('name'),
            'type' => $this->type?->toString(),
            'short_description' => $this->translate('short_description'),
            'sale_percent' => $this->sale_percent,
            'end_date' => $endDate->format('d.m.Y'),
            'days_left' => round($now->diffInDays($endDate, false)),
            'items' => $items,
        ];
    }

}
