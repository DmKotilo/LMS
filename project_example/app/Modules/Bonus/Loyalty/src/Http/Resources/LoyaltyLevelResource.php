<?php

namespace App\Modules\Bonus\Loyalty\src\Http\Resources;

use App\Modules\Bonus\Bonus\src\Enums\BonusStatusEnum;
use App\Modules\Bonus\Bonus\src\Enums\BonusTypeEnum;
use App\Modules\Product\Attribute\src\Http\Resources\ProductAttributeResource;
use App\Modules\Product\Tag\src\Http\Resources\ProductTagResource;
use App\Modules\Product\Tag\src\Http\Resources\TagResource;
use Attribute\Http\Resources\AttributeResource;
use Banner\Models\Banner;
use Brand\Http\Resources\BrandResource;
use Carbon\Carbon;
use Category\Http\Resources\CategoryResource;
use Category\Models\Category;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Order\src\Enums\OrderStatusEnum;
use Loyalty\Models\LoyaltyLevel;
use Product\Models\Product;

class LoyaltyLevelResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = LoyaltyLevel::class;
    public function toArray(Request $request): array
    {
        $user = auth()->user();
        $now = Carbon::now();
        
        // Используем фиксированные 90 дней (3 месяца) для расчета уровня лояльности
        $durationDays = 90;
        $totalSpent = $user->orders()
            ->where('status', OrderStatusEnum::Completed->value)
            ->where('created_at', '>=', $now->copy()->subDays($durationDays))
            ->where('created_at', '<=', $now)
            ->sum('price_final');

        $previousLevel = LoyaltyLevel::where('coefficient', '<', $this->coefficient)
            ->orderBy('coefficient', 'desc')
            ->first();

        $nextLevel = LoyaltyLevel::where('coefficient', '>', $this->coefficient)
            ->orderBy('coefficient', 'asc')
            ->first();

        $from = $previousLevel ? (int) $previousLevel->coefficient + 1 : 0;
        $to = $nextLevel ? (int) $this->coefficient : null;

        if ($to !== null) {
            $progress = (($totalSpent - $from) / ($to - $from)) * 100;
            $progress = max(0, min(100, $progress));
        } else {
            $userCurrentLevelId = $user->loyaltyLevel?->loyalty_level_id ?? null;

            if ($userCurrentLevelId === $this->id) {
                $progress = 100;
            } else {
                $progress = 0;
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'duration_days' => (int) $this->duration_days,
            'discount_percent' => (float) $this->discount_percent,
            'coefficient' => (int) $this->coefficient,
            'from' => $from,
            'to' => $to,
            'progress' => round($progress, 2),
            'link_url' => $this->link_url,
            'button_text' => $this->button_text,
        ];
    }


}
