<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{
    public function view(User $user, Department $department): bool
    {
        return $user->sharesDepartment($department->id);
    }

    public function updateRules(User $user, Department $department): bool
    {
        return $user->role === Role::Admin || ($user->role === Role::Scheduler && $user->department_id === $department->id);
    }
}
