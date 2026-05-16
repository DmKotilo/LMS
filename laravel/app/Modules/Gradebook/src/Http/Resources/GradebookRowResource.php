<?php

namespace Gradebook\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradebookRowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_name' => $this->student_name,
            'group_name' => $this->group_name,
            'semester' => $this->semester,
            'module1_score' => $this->module1_score,
            'module2_score' => $this->module2_score,
            'total_score' => $this->total_score,
            'final_grade' => $this->final_grade,
            'gradebook' => new GradebookResource($this->whenLoaded('gradebook')),
        ];
    }
}
