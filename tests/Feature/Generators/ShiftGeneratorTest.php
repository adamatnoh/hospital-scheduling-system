<?php

use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Enums\Shift;
use App\Models\PublicHoliday;
use App\Models\ScheduleRequest;
use App\Services\Scheduling\ShiftGenerator;

it('gives each doctor one shift a day and covers morning, evening and night', function () {
    [$department] = rosterDepartment(4);

    $run = app(ShiftGenerator::class)->preview($department, 2026, 3);
    $duplicates = $run->assignments
        ->groupBy(fn ($assignment) => $assignment->user_id.'-'.$assignment->date->toDateString())
        ->filter(fn ($group) => $group->count() > 1);

    $monday = $run->assignments->filter(fn ($assignment) => $assignment->date->toDateString() === '2026-03-02');

    expect($duplicates)->toBeEmpty();
    expect($monday->pluck('shift')->map(fn ($shift) => $shift->value)->unique()->sort()->values()->all())
        ->toEqualCanonicalizing([Shift::Morning->value, Shift::Evening->value, Shift::Night->value]);
    expect($run->assignments->contains(fn ($assignment) => $assignment->date->isWeekend()))->toBeFalse();
});

it('skips approved leave and public holidays', function () {
    [$department, $doctors] = rosterDepartment(3);
    $doctor = $doctors->first();

    ScheduleRequest::query()->create([
        'user_id' => $doctor->id,
        'department_id' => $department->id,
        'type' => RequestType::Leave,
        'start_date' => '2026-03-04',
        'end_date' => '2026-03-04',
        'reason' => 'Appointment',
        'status' => RequestStatus::Approved,
    ]);
    PublicHoliday::query()->create([
        'date' => '2026-03-23',
        'name' => 'Johor holiday',
        'state' => 'Johor',
    ]);

    $run = app(ShiftGenerator::class)->preview($department, 2026, 3);

    expect($run->assignments->contains(fn ($assignment) => $assignment->user_id === $doctor->id && $assignment->date->toDateString() === '2026-03-04'))->toBeFalse();
    expect($run->assignments->contains(fn ($assignment) => $assignment->date->toDateString() === '2026-03-23'))->toBeFalse();
});

it('keeps the three shift totals within one of each other', function () {
    [$department] = rosterDepartment(4);

    $run = app(ShiftGenerator::class)->preview($department, 2026, 3);
    $totals = $run->assignments->countBy(fn ($assignment) => $assignment->shift->value);

    expect($totals->max() - $totals->min())->toBeLessThanOrEqual(1);
});
