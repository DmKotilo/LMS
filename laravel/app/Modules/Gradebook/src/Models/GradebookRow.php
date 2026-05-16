<?php

namespace Gradebook\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use User\Models\User;

class GradebookRow extends Model
{
    protected $fillable = [
        'gradebook_id',
        'student_id',
        'student_name',
        'group_name',
        'semester',
        'module1_score',
        'module2_score',
        'total_score',
        'final_grade',
        'raw_data',
    ];

    protected $casts = [
        'module1_score' => 'decimal:2',
        'module2_score' => 'decimal:2',
        'total_score' => 'decimal:2',
        'raw_data' => 'array',
    ];

    public function gradebook(): BelongsTo
    {
        return $this->belongsTo(Gradebook::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function scopeForStudent(Builder $query, User $student): Builder
    {
        return $query->where('student_id', $student->id);
    }

    public function scopeFilterSemester(Builder $query, ?string $semester): Builder
    {
        if ($semester === null || $semester === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($semester) {
            $q->where('semester', $semester)
                ->orWhereHas('gradebook', fn (Builder $gq) => $gq->where('semester', $semester));
        });
    }
}
