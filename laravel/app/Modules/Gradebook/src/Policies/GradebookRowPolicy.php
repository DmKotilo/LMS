<?php

namespace Gradebook\Policies;

use Gradebook\Models\GradebookRow;
use User\Enums\UserRole;
use User\Models\User;

class GradebookRowPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Student
            && $user->studentProfile()->exists();
    }

    public function view(User $user, GradebookRow $row): bool
    {
        return $user->role === UserRole::Student
            && $row->student_id === $user->id;
    }
}
