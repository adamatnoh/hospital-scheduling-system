<?php

use App\Enums\RequestStatus;
use App\Enums\Role;
use App\Models\Department;
use App\Models\ScheduleRequest;
use App\Models\User;

it('sends guests to the login page', function () {
    $this->get('/dashboard')->assertRedirect(route('login'));
    $this->get('/staff')->assertRedirect(route('login'));
});

it('stops a doctor opening staff management or the generator', function () {
    [$department] = rosterDepartment(1);
    $doctor = User::query()->where('department_id', $department->id)->first();

    $this->actingAs($doctor)
        ->get('/staff')
        ->assertRedirect(route('unauthorized'));

    $this->actingAs($doctor)
        ->get('/generate/on_call')
        ->assertRedirect(route('unauthorized'));
});

it('lets a scheduler review their department and blocks another department', function () {
    [$department, $doctors] = rosterDepartment(1);
    $scheduler = User::factory()->scheduler()->create(['department_id' => $department->id]);
    $other = Department::factory()->create(['name' => 'Emergency '.fake()->numerify('###')]);
    $outsider = User::factory()->scheduler()->create(['department_id' => $other->id]);

    $application = ScheduleRequest::query()->create([
        'user_id' => $doctors->first()->id,
        'department_id' => $department->id,
        'type' => 'leave',
        'start_date' => now()->addDay()->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
        'reason' => 'Course',
        'status' => RequestStatus::Pending,
    ]);

    $this->actingAs($scheduler)->get('/review')->assertOk();

    $this->actingAs($outsider)
        ->post(route('review.decide', $application), [
            'decision' => 'approved',
        ])
        ->assertForbidden();

    expect($application->refresh()->status)->toBe(RequestStatus::Pending);
});

it('records a leave request against the signed-in doctor', function () {
    [$department] = rosterDepartment(1);
    $doctor = User::query()->where('department_id', $department->id)->first();
    $other = User::factory()->create(['department_id' => $department->id, 'role' => Role::Regular]);

    $this->actingAs($doctor)->post('/leave', [
        'type' => 'leave',
        'user_id' => $other->id,
        'start_date' => now()->addDays(3)->toDateString(),
        'end_date' => now()->addDays(4)->toDateString(),
        'reason' => 'Family event',
    ])->assertRedirect(route('calendar.history'));

    $application = ScheduleRequest::query()->first();

    expect($application->user_id)->toBe($doctor->id);
    expect($application->status)->toBe(RequestStatus::Pending);
});

it('requires a reason when a request is rejected', function () {
    [$department, $doctors] = rosterDepartment(1);
    $scheduler = User::factory()->scheduler()->create(['department_id' => $department->id]);
    $application = ScheduleRequest::query()->create([
        'user_id' => $doctors->first()->id,
        'department_id' => $department->id,
        'type' => 'on_call',
        'start_date' => now()->addDay()->toDateString(),
        'end_date' => now()->addDay()->toDateString(),
        'reason' => 'Prefer Friday',
        'status' => RequestStatus::Pending,
    ]);

    $this->actingAs($scheduler)
        ->from('/review')
        ->post(route('review.decide', $application), ['decision' => 'rejected'])
        ->assertSessionHasErrors('rejection_reason');
});
