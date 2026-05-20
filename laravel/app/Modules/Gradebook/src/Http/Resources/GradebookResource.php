<?php

namespace Gradebook\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradebookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'discipline' => $this->discipline,
            'direction_code' => $this->direction_code,
            'group_name' => $this->group_name,
            'semester' => $this->semester,
            'academic_year' => $this->academic_year,
            'teacher' => $this->whenLoaded('teacher', fn () => [
                'id' => $this->teacher->id,
                'full_name' => $this->teacher->fullName(),
            ]),
            'original_filename' => $this->original_filename,
            'rows_count' => $this->whenCounted('rows'),
            'uploaded_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
