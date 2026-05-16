<?php

namespace App\Modules\Banner\src\Http\Resources;

use Banner\Models\Banner;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Support\Carbon;

class BannerResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = Banner::class;
    public function toArray(Request $request): array
    {
        $promotionDaysLeft = $this->getPromotionDaysLeft();

        return [
            'image' => new FileResource($this->image),
            'id' => $this->id,
            'title' => html_entity_decode(strip_tags($this->translate('title'))),
            'description' => nl2br(
                html_entity_decode(strip_tags($this->translate('description')))
            ),
            'addition_description' => html_entity_decode(strip_tags($this->translate('addition_description'))),
            'link_url' => $this->link_url,
            'button_text' => $this->translate('button_text'),
            'promotion_days_left' => $promotionDaysLeft,
        ];
    }

    protected function getPromotionDaysLeft(): ?int
    {
        $promotion = $this->whenLoaded('globalPromotion');

        if ($promotion instanceof MissingValue) {
            $promotion = null;
        }

        $promotion = $promotion ?? $this->globalPromotion;

        if (!$promotion || !$promotion->ends_at) {
            return null;
        }

        $endsAt = Carbon::parse($promotion->ends_at)->endOfDay();
        $now = Carbon::now();

        if ($endsAt->isPast()) {
            return 0;
        }

        return max(0, $now->diffInDays($endsAt, false));
    }
}
