<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Department;
use App\Models\ScheduleRun;
use App\Models\User;

class ScheduleRunPolicy
{
    public function view(User $user, ScheduleRun $run): bool
    {
        return $user->sharesDepartment($run->department_id);
    }

    public function generate(User $user, Department $department): bool
    {
        return $user->sharesDepartment($department->id)
            && in_array($user->role, [Role::Scheduler, Role::Admin], true);
    }

    public function publish(User $user, ScheduleRun $run): bool
    {
        return $this->generate($user, $run->department);
    }
}
