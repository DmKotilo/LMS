<?php

namespace Order\Http\Resources;

use App\Modules\Cart\src\Traits\GetCart;
use Combo\Models\Combo;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Order\Models\TransactionMethod;


class TransactionMethodResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */

    public static $model = TransactionMethod::class;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->translate('name'),
            'description' => $this->description,
            'icon' => new FileResource($this->image),
        ];
    }



}
