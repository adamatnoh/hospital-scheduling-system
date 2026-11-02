<?php

use App\Enums\AssignmentType;
use App\Models\ScheduleAssignment;
use App\Models\ScheduleRun;
use App\Notifications\OnCallReminder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

function onCallDuty($department, $user, string $date, bool $published = true): ScheduleAssignment
{
    $day = Carbon::parse($date);
    $run = ScheduleRun::query()->firstOrCreate(
        ['department_id' => $department->id, 'type' => 'on_call', 'year' => $day->year, 'month' => $day->month],
        ['status' => 'published', 'published_at' => now()],
    );

    return ScheduleAssignment::query()->create([
        'schedule_run_id' => $run->id,
        'type' => AssignmentType::OnCall,
        'user_id' => $user->id,
        'department_id' => $department->id,
        'date' => $day->toDateString(),
        'is_published' => $published,
    ]);
}

it('reminds only the doctors on call tomorrow', function () {
    Notification::fake();
    [$department, $doctors] = rosterDepartment(3);
    [$tomorrow, $later, $draft] = $doctors->all();

    onCallDuty($department, $tomorrow, now()->addDay()->toDateString());
    onCallDuty($department, $later, now()->addDays(3)->toDateString());
    onCallDuty($department, $draft, now()->addDay()->toDateString(), published: false);

    $this->artisan('hss:remind-on-call')->expectsOutputToContain('Sent 1 on-call reminder(s)')->assertSuccessful();

    Notification::assertSentTo($tomorrow, OnCallReminder::class);
    Notification::assertNotSentTo([$later, $draft], OnCallReminder::class);
});

it('can remind for a chosen date', function () {
    Notification::fake();
    [$department, $doctors] = rosterDepartment(1);
    $date = now()->addDays(5)->toDateString();
    onCallDuty($department, $doctors->first(), $date);

    $this->artisan('hss:remind-on-call', ['--date' => $date])->assertSuccessful();

    Notification::assertSentTo($doctors->first(), OnCallReminder::class);
});

it('registers the reminder and housekeeping on the scheduler', function () {
    $events = collect(app(Schedule::class)->events())
        ->map(fn ($event) => [$event->command, $event->expression, $event->timezone]);

    $reminder = $events->first(fn ($e) => str_contains($e[0], 'hss:remind-on-call'));
    expect($reminder)->not->toBeNull()
        ->and($reminder[1])->toBe('0 8 * * *')
        ->and((string) $reminder[2])->toBe('Asia/Kuala_Lumpur');

    foreach (['hss:expire-announcements', 'hss:prune', 'queue:prune-failed', 'auth:clear-resets'] as $command) {
        expect($events->contains(fn ($e) => str_contains($e[0], $command)))->toBeTrue($command.' is scheduled');
    }
});
