<?php

namespace User\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'student_id_number' => $this->student_id_number,
            'group' => new StudentGroupResource($this->whenLoaded('group')),
        ];
    }
}
