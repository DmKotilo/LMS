<?php

namespace Gradebook\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradebookDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            ...(new GradebookResource($this->resource))->toArray($request),
            'rows' => GradebookRowResource::collection($this->whenLoaded('rows')),
        ];
    }
}
