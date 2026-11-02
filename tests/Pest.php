<?php

use App\Enums\Role;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/**
 * @return array{0: Department, 1: Collection<int, User>}
 */
function rosterDepartment(int $doctors = 4, array $rules = []): array
{
    $department = Department::factory()->create(array_merge([
        'name' => 'Orthopedics '.fake()->unique()->numerify('###'),
        'doctors_per_on_call_day' => 2,
        'monthly_on_call_cap' => 10,
        'ward_block_days' => 14,
        'holiday_state' => 'Johor',
    ], $rules));

    $users = User::factory()->count($doctors)->create([
        'department_id' => $department->id,
        'role' => Role::Regular,
    ]);

    return [$department, $users];
}
