<?php

use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Models\ScheduleRequest;
use App\Models\Ward;
use App\Services\Scheduling\WardRotationGenerator;

it('rotates a doctor through wards in 14 day blocks without repeating a day', function () {
    [$department, $doctors] = rosterDepartment(2);
    $first = Ward::factory()->create(['department_id' => $department->id, 'name' => 'Male Ortho']);
    $second = Ward::factory()->create(['department_id' => $department->id, 'name' => 'Trauma']);

    $run = app(WardRotationGenerator::class)->preview($department, 2026, 3);
    $mine = $run->assignments->where('user_id', $doctors->first()->id)->sortBy('date')->values();

    expect($mine->map(fn ($assignment) => $assignment->date->toDateString())->duplicates())->toBeEmpty();
    expect($mine->first()->ward_id)->toBe($first->id);
    expect($mine->firstWhere(fn ($assignment) => $assignment->date->toDateString() === '2026-03-14')->ward_id)->toBe($first->id);
    expect($mine->firstWhere(fn ($assignment) => $assignment->date->toDateString() === '2026-03-15')->ward_id)->toBe($second->id);
});

it('does not place a doctor on a ward during approved leave', function () {
    [$department, $doctors] = rosterDepartment(1);
    Ward::factory()->create(['department_id' => $department->id, 'name' => 'Male Ortho']);
    $doctor = $doctors->first();

    ScheduleRequest::query()->create([
        'user_id' => $doctor->id,
        'department_id' => $department->id,
        'type' => RequestType::Leave,
        'start_date' => '2026-03-01',
        'end_date' => '2026-03-02',
        'reason' => 'Clinic',
        'status' => RequestStatus::Approved,
    ]);

    $run = app(WardRotationGenerator::class)->preview($department, 2026, 3);
    $dates = $run->assignments->map(fn ($assignment) => $assignment->date->toDateString());

    expect($dates)->not->toContain('2026-03-01');
    expect($dates)->not->toContain('2026-03-02');
    expect($dates)->toContain('2026-03-03');
});
