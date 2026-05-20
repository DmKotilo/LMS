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
            'module1_theory' => data_get($this->raw_data, 'module1_theory'),
            'module1_practice' => data_get($this->raw_data, 'module1_practice'),
            'module2_theory' => data_get($this->raw_data, 'module2_theory'),
            'module2_practice' => data_get($this->raw_data, 'module2_practice'),
            'mrs_score' => data_get($this->raw_data, 'mrs_total', $this->module1_score + $this->module2_score),
            'exam_score' => $this->exam_score,
            'total_score' => $this->total_score,
            'final_grade' => $this->final_grade,
            'gradebook' => new GradebookResource($this->whenLoaded('gradebook')),
        ];
    }
}
