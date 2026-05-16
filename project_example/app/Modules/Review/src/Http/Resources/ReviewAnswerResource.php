<?php

namespace App\Modules\Review\src\Http\Resources;

use App\Modules\Review\src\Models\ReviewAnswer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class ReviewAnswerResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = ReviewAnswer::class;
    public function toArray(Request $request): array
    {
        return [
            'text' => $this->text,
        ];
    }
}
