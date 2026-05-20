<?php

namespace Gradebook\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use User\Enums\UserRole;
use User\Models\User;

class Gradebook extends Model
{
    protected $fillable = [
        'title',
        'discipline',
        'direction_code',
        'group_name',
        'semester',
        'academic_year',
        'teacher_id',
        'original_filename',
        'storage_path',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(GradebookRow::class);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return match ($user->role) {
            UserRole::Administrator => $query,
            UserRole::Teacher => $query->where('teacher_id', $user->id),
            default => $query->whereRaw('0 = 1'),
        };
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $search = '%'.$filters['search'].'%';
            $query->where(function (Builder $q) use ($search) {
                $q->where('discipline', 'ilike', $search)
                    ->orWhere('group_name', 'ilike', $search)
                    ->orWhere('title', 'ilike', $search)
                    ->orWhere('original_filename', 'ilike', $search)
                    ->orWhereHas('teacher', function (Builder $tq) use ($search) {
                        $tq->where('last_name', 'ilike', $search)
                            ->orWhere('first_name', 'ilike', $search)
                            ->orWhere('second_name', 'ilike', $search);
                    });
            });
        }

        if (! empty($filters['discipline'])) {
            $query->where('discipline', 'ilike', '%'.$filters['discipline'].'%');
        }

        if (! empty($filters['group_name'])) {
            $query->where('group_name', 'ilike', '%'.$filters['group_name'].'%');
        }

        if (! empty($filters['semester'])) {
            $query->where('semester', $filters['semester']);
        }

        if (! empty($filters['academic_year'])) {
            $query->where('academic_year', $filters['academic_year']);
        }

        if (! empty($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        return $query;
    }

    public function scopeApplySort(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {
            'oldest' => $query->orderBy('created_at'),
            'discipline_asc' => $query->orderBy('discipline')->orderByDesc('created_at'),
            'discipline_desc' => $query->orderByDesc('discipline')->orderByDesc('created_at'),
            'group_asc' => $query->orderBy('group_name')->orderByDesc('created_at'),
            'group_desc' => $query->orderByDesc('group_name')->orderByDesc('created_at'),
            default => $query->orderByDesc('created_at'),
        };
    }
}
