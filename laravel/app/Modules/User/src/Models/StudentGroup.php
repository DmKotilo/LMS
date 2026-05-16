<?php

namespace User\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use User\Enums\EducationForm;

class StudentGroup extends Model
{
    protected $fillable = [
        'name',
        'education_form',
        'course',
    ];

    protected $casts = [
        'education_form' => EducationForm::class,
        'course' => 'integer',
    ];

    public function studentProfiles(): HasMany
    {
        return $this->hasMany(StudentProfile::class, 'group_id');
    }
}
