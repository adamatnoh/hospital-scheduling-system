<?php

use App\Enums\AssignmentType;
use App\Enums\RunStatus;
use App\Enums\Shift;
use App\Models\ScheduleAssignment;
use App\Models\ScheduleRun;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Support\Facades\DB;

/**
 * One published run with an on-call day and a night shift for $doctor, a
 * published on-call day for $other, and an unpublished day for $doctor.
 *
 * @return array{0: User, 1: User}
 */
function feedFixture(): array
{
    [$department, $users] = rosterDepartment(2);
    [$doctor, $other] = [$users[0], $users[1]];
    $ward = Ward::factory()->create(['department_id' => $department->id, 'name' => 'Male Ortho']);
    $month = now(config('app.timezone'))->startOfMonth();

    $run = fn (AssignmentType $type, RunStatus $status) => ScheduleRun::create([
        'department_id' => $department->id,
        'type' => $type,
        'year' => $month->year,
        'month' => $month->month,
        'status' => $status,
    ]);
    $onCall = $run(AssignmentType::OnCall, RunStatus::Published);
    $shifts = $run(AssignmentType::Shift, RunStatus::Published);
    $draft = $run(AssignmentType::Ward, RunStatus::Preview);

    $make = fn (ScheduleRun $r, User $u, int $day, array $extra = []) => ScheduleAssignment::create(array_merge([
        'schedule_run_id' => $r->id,
        'type' => $r->type,
        'user_id' => $u->id,
        'department_id' => $department->id,
        'date' => $month->copy()->addDays($day)->toDateString(),
        'is_published' => $r->status === RunStatus::Published,
    ], $extra));

    $make($onCall, $doctor, 2);
    $make($shifts, $doctor, 3, ['shift' => Shift::Night]);
    $make($onCall, $other, 4);
    $make($draft, $doctor, 5, ['ward_id' => $ward->id]);

    return [$doctor, $other];
}

function feedPath(User $user): string
{
    return '/calendar/feed/'.$user->calendarFeedToken().'.ics';
}

it('serves a valid calendar for a good token with no session', function () {
    [$doctor] = feedFixture();

    $response = $this->get(feedPath($doctor));

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/calendar; charset=utf-8')
        ->assertHeaderMissing('Set-Cookie');
    expect($response->headers->get('Cache-Control'))->toContain('max-age=3600')->toContain('private')
        ->and($response->headers->get('ETag'))->not->toBeNull();

    $ics = $response->getContent();
    expect($ics)->toStartWith("BEGIN:VCALENDAR\r\n")
        ->toEndWith("END:VCALENDAR\r\n")
        ->toContain('X-WR-CALNAME:HSS – '.$doctor->displayName())
        ->toContain("REFRESH-INTERVAL;VALUE=DURATION:PT1H\r\n")
        ->toContain("X-PUBLISHED-TTL:PT1H\r\n")
        ->toContain("BEGIN:VTIMEZONE\r\nTZID:Asia/Kuala_Lumpur\r\n")
        ->toContain('TZOFFSETTO:+0800');
    expect(substr_count($ics, 'BEGIN:VEVENT'))->toBe(substr_count($ics, 'END:VEVENT'));

    foreach (explode("\r\n", $ics) as $line) {
        expect(strlen($line))->toBeLessThanOrEqual(75);
    }
});

it('returns 404 for an unknown or malformed token', function () {
    [$doctor] = feedFixture();
    $doctor->calendarFeedToken();

    $this->get('/calendar/feed/'.str_repeat('a', 40).'.ics')->assertNotFound();
    $this->get('/calendar/feed/short.ics')->assertNotFound();
});

it('stores only a hash and the encrypted token', function () {
    [$doctor] = feedFixture();
    $token = $doctor->calendarFeedToken();

    $row = DB::table('users')->where('id', $doctor->id)->first();
    expect($row->calendar_feed_hash)->toBe(hash('sha256', $token))
        ->and($row->calendar_feed_token)->not->toContain($token);
});

it('invalidates the old link on reset', function () {
    [$doctor] = feedFixture();
    $old = feedPath($doctor);
    $this->get($old)->assertOk();

    $this->actingAs($doctor)->post(route('calendar.subscribe.reset'))
        ->assertRedirect(route('calendar.subscribe'));

    $this->get($old)->assertNotFound();
    $this->get(feedPath($doctor->fresh()))->assertOk();
});

it('contains only that user\'s published duties with stable UIDs', function () {
    [$doctor, $other] = feedFixture();
    $mine = ScheduleAssignment::where('user_id', $doctor->id)->where('is_published', true)->pluck('id');
    $hidden = ScheduleAssignment::where(fn ($q) => $q->where('user_id', $other->id)->orWhere('is_published', false))->pluck('id');

    $ics = $this->get(feedPath($doctor))->getContent();

    expect(substr_count($ics, 'BEGIN:VEVENT'))->toBe($mine->count());
    foreach ($mine as $id) {
        expect($ics)->toContain("UID:hss-assignment-{$id}@hss\r\n");
    }
    foreach ($hidden as $id) {
        expect($ics)->not->toContain("UID:hss-assignment-{$id}@hss\r\n");
    }
    expect($this->get(feedPath($doctor))->getContent())->toBe($ics);
});

it('writes on-call days as all-day and shifts as timed events', function () {
    [$doctor] = feedFixture();
    $onCall = ScheduleAssignment::where('user_id', $doctor->id)->where('type', AssignmentType::OnCall)->first();
    $night = ScheduleAssignment::where('user_id', $doctor->id)->where('type', AssignmentType::Shift)->first();

    $ics = $this->get(feedPath($doctor))->getContent();

    expect($ics)
        ->toContain('DTSTART;VALUE=DATE:'.$onCall->date->format('Ymd')."\r\n")
        ->toContain('DTEND;VALUE=DATE:'.$onCall->date->copy()->addDay()->format('Ymd')."\r\n")
        ->toContain('DTSTART;TZID=Asia/Kuala_Lumpur:'.$night->date->format('Ymd')."T210000\r\n")
        ->toContain('DTEND;TZID=Asia/Kuala_Lumpur:'.$night->date->copy()->addDay()->format('Ymd')."T070000\r\n")
        ->toContain('SUMMARY:Night shift');
});

it('shows the subscribe links to the signed-in user only', function () {
    [$doctor] = feedFixture();
    $this->get(route('calendar.subscribe'))->assertRedirect(route('login'));

    $response = $this->actingAs($doctor)->get(route('calendar.subscribe'))->assertOk();
    $token = $doctor->fresh()->calendarFeedToken();
    $webcal = preg_replace('#^https?://#', 'webcal://', url('/calendar/feed/'.$token.'.ics'));

    $response->assertSee(url('/calendar/feed/'.$token.'.ics'), false)
        ->assertSee($webcal, false)
        ->assertSee('https://calendar.google.com/calendar/r?cid='.rawurlencode($webcal), false)
        ->assertSee('Reset link')
        ->assertSee('Download .ics');
});

it('still downloads a one-off file', function () {
    [$doctor] = feedFixture();

    $this->actingAs($doctor)->get(route('ical.download'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
});
