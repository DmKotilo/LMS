<?php

namespace Manufacturer\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Manufacturer\Models\Manufacturer;

class ManufacturerShortResource extends JsonResource
{
    public static $model = Manufacturer::class;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
