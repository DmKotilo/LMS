<?php

namespace App\Modules\Promotion\src\Http\Resources;


use Carbon\Carbon;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Promotion\Models\Promotion;

class PromotionCardResource extends JsonResource
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
        return [
            'id' => $this->id,
            'image' => new FileResource($this->image),
            'name' => $this->translate('name'),
            'short_description' => $this->translate('short_description'),
            'type' => $this->type?->toString(),
            'sale_percent' => $this->sale_percent,
            'end_date' => $endDate->format('d.m.Y'),
            'days_left' => round($now->diffInDays($endDate, false)),

            'seo_description' => $this->seo_description,
            'seo_title' => $this->seo_title,
            ];
    }

}
