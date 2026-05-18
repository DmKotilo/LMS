<?php

namespace User\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'role' => $this->role->value,
            'role_label' => $this->role->label(),
            'first_name' => $this->first_name,
            'second_name' => $this->second_name,
            'last_name' => $this->last_name,
            'full_name' => $this->fullName(),
            'email' => $this->email,
            'new_email' => $this->when((bool) $this->new_email, $this->new_email),
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'phone' => $this->phone,
            'is_active' => $this->is_active,
            'default_path' => $this->defaultApiPath(),
            'student_profile' => new StudentProfileResource($this->whenLoaded('studentProfile')),
            'gradebooks_count' => $this->whenCounted('gradebooksAsTeacher'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
