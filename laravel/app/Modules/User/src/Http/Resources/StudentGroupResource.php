<?php

namespace User\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'education_form' => $this->education_form->value,
            'education_form_label' => $this->education_form->label(),
            'course' => $this->course,
        ];
    }
}
