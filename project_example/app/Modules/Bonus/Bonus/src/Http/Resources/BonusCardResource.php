<?php

namespace App\Modules\Bonus\Bonus\src\Http\Resources;

use App\Modules\Bonus\Bonus\src\Enums\BonusStatusEnum;
use App\Modules\Bonus\Bonus\src\Enums\BonusTypeEnum;
use App\Modules\Product\Review\src\Http\Resources\ReviewResource;
use Bonus\Models\BonusCard;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BonusCardResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = BonusCard::class;
    public function toArray(Request $request): array
    {
        $now = Carbon::now();

        // Доступные бонусы = сумма remaining_amount активных начислений
        // с учетом active_date и expires_at
        $bonusCount = auth()->user()?->bonusHistories()
            ->where('type', BonusTypeEnum::Accrual)
            ->where('status', BonusStatusEnum::Active)
            ->where('remaining_amount', '>', 0)
            ->where('active_date', '<=', $now)
            ->where(function ($query) use ($now) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', $now);
            })
            ->sum('remaining_amount');

        return [
            'id' => $this->id,
            'number' => trim(chunk_split($this->number, 4, ' ')),
            'amount' => (int) round($bonusCount),
            'level' => $this->user->loyaltyLevel?->loyaltyLevel->name,
            'cashback' => (int) $this->user->loyaltyLevel?->loyaltyLevel->discount_percent
        ];
    }

}
