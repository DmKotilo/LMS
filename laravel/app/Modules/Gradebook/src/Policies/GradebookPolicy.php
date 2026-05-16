<?php

namespace Gradebook\Policies;

use Gradebook\Models\Gradebook;
use User\Enums\UserRole;
use User\Models\User;

class GradebookPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::Administrator, UserRole::Teacher], true);
    }

    public function view(User $user, Gradebook $gradebook): bool
    {
        return match ($user->role) {
            UserRole::Administrator => true,
            UserRole::Teacher => $gradebook->teacher_id === $user->id,
            default => false,
        };
    }

    public function delete(User $user, Gradebook $gradebook): bool
    {
        return $user->role === UserRole::Administrator;
    }

    public function export(User $user, Gradebook $gradebook): bool
    {
        return $this->view($user, $gradebook);
    }
}
