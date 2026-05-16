<?php

namespace Address\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Knuckles\Scribe\Attributes\ResponseField;

class AddressCollection extends ResourceCollection
{



    public function toArray(Request $request): array
    {
        return [
            "data" => $this->collection,
        ];
    }
}
