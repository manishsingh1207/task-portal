<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Manager', 'Staff']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Manager']);
    }

    public function update(User $user, Task $task): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager'])) {
            return true;
        }
        if ($user->hasRole('Staff') && $task->user_id === $user->id) {
            return true;
        }
        return false;
    }
}
