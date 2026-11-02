<?php

use App\Enums\AssignmentType;
use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Models\ScheduleRequest;
use App\Services\Scheduling\OnCallGenerator;

it('does not book a doctor twice on the same day', function () {
    [$department] = rosterDepartment();

    $run = app(OnCallGenerator::class)->preview($department, 2026, 3);

    $duplicates = $run->assignments
        ->groupBy(fn ($assignment) => $assignment->user_id.'-'.$assignment->date->toDateString())
        ->filter(fn ($group) => $group->count() > 1);

    expect($duplicates)->toBeEmpty();
    expect($run->assignments->every(fn ($assignment) => $assignment->type === AssignmentType::OnCall))->toBeTrue();
});

it('keeps a doctor off the on-call roster while they are on leave', function () {
    [$department, $doctors] = rosterDepartment();
    $doctor = $doctors->first();

    ScheduleRequest::query()->create([
        'user_id' => $doctor->id,
        'department_id' => $department->id,
        'type' => RequestType::Leave,
        'start_date' => '2026-03-10',
        'end_date' => '2026-03-12',
        'reason' => 'Course',
        'status' => RequestStatus::Approved,
    ]);

    $run = app(OnCallGenerator::class)->preview($department, 2026, 3);
    $dates = $run->assignments->where('user_id', $doctor->id)->map(fn ($assignment) => $assignment->date->toDateString());

    expect($dates->intersect(['2026-03-10', '2026-03-11', '2026-03-12']))->toBeEmpty();
});

it('stops assigning a doctor once the monthly cap is reached', function () {
    [$department] = rosterDepartment(3, ['monthly_on_call_cap' => 2, 'doctors_per_on_call_day' => 2]);

    $run = app(OnCallGenerator::class)->preview($department, 2026, 3);
    $counts = $run->assignments->countBy('user_id');

    expect($counts->max())->toBeLessThanOrEqual(2);
    expect($run->fairness['coverage_gaps'])->not->toBeEmpty();
});

it('places an approved on-call request on the requested day', function () {
    [$department, $doctors] = rosterDepartment();
    $doctor = $doctors->last();

    ScheduleRequest::query()->create([
        'user_id' => $doctor->id,
        'department_id' => $department->id,
        'type' => RequestType::OnCall,
        'start_date' => '2026-03-05',
        'end_date' => '2026-03-05',
        'reason' => 'Prefer this day',
        'status' => RequestStatus::Approved,
    ]);

    $run = app(OnCallGenerator::class)->preview($department, 2026, 3);

    expect($run->assignments->contains(fn ($assignment) => $assignment->user_id === $doctor->id && $assignment->date->toDateString() === '2026-03-05'))->toBeTrue();
});
