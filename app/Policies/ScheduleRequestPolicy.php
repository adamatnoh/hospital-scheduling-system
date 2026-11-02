<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\ScheduleRequest;
use App\Models\User;

class ScheduleRequestPolicy
{
    public function view(User $user, ScheduleRequest $request): bool
    {
        if ($user->id === $request->user_id) {
            return true;
        }

        return $user->role !== Role::Regular && $user->sharesDepartment($request->department_id);
    }

    public function create(User $user): bool
    {
        return $user->role?->isDoctor() && $user->department_id !== null;
    }

    public function decide(User $user, ScheduleRequest $request): bool
    {
        return $user->sharesDepartment($request->department_id)
            && in_array($user->role, [Role::Scheduler, Role::Admin], true);
    }
}
