<?php

namespace App\Modules\Bonus\Bonus\src\Http\Resources;

use App\Modules\Product\Attribute\src\Http\Resources\ProductAttributeResource;
use App\Modules\Product\Review\src\Http\Resources\ReviewResource;
use App\Modules\Product\Tag\src\Http\Resources\ProductTagResource;
use App\Modules\Product\Tag\src\Http\Resources\TagResource;
use Attribute\Http\Resources\AttributeResource;
use Banner\Models\Banner;
use Bonus\Models\UserBonusHistory;
use Brand\Http\Resources\BrandResource;
use Carbon\Carbon;
use Category\Http\Resources\CategoryResource;
use Category\Models\Category;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Product\Models\Product;

class BonusHistoryResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = UserBonusHistory::class;
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => (int) round((float) $this->amount),
            'type' => $this->type->toString(),
            'status' => $this->status->toString(),
            'active_date' => $this->active_date
                ? Carbon::parse($this->active_date)->format('d.m.Y')
                : null,
            'end_date' => $this->expires_at
                ? Carbon::parse($this->expires_at)->format('d.m.Y')
                : null,
            'is_manual' => $this->is_manual,
            'reason' => $this->reason?->toString(),
            'comment' => $this->comment,
            ];
    }

}
