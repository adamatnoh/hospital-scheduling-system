<?php

use App\Models\ScheduleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('the app runs on hospital time', function () {
    expect(config('app.timezone'))->toBe('Asia/Kuala_Lumpur')
        ->and(now()->getTimezone()->getName())->toBe('Asia/Kuala_Lumpur');
});

test('the database session uses the same clock as the app', function () {
    $dbNow = DB::selectOne('select to_char(localtimestamp, \'YYYY-MM-DD HH24:MI\') as t')->t;

    expect(abs(now()->diffInMinutes(Carbon\Carbon::parse($dbNow), false)))->toBeLessThan(2);
})->skip(fn () => DB::getDriverName() !== 'pgsql', 'Postgres only');

test('date-only roster days never shift', function () {
    $assignment = new ScheduleAssignment(['date' => '2026-11-01']);

    expect($assignment->date->toDateString())->toBe('2026-11-01')
        ->and($assignment->date->format('H:i'))->toBe('00:00');
});
