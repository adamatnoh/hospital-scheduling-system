<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\ScheduleAssignment;
use App\Models\User;

class ScheduleAssignmentPolicy
{
    public function view(User $user, ScheduleAssignment $assignment): bool
    {
        return $user->id === $assignment->user_id || $user->sharesDepartment($assignment->department_id);
    }

    public function update(User $user, ScheduleAssignment $assignment): bool
    {
        return $user->sharesDepartment($assignment->department_id)
            && in_array($user->role, [Role::Scheduler, Role::Admin], true);
    }
}
