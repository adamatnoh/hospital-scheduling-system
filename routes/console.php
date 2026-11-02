<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| HSS scheduled jobs
|--------------------------------------------------------------------------
| Times are hospital time (APP_TIMEZONE, Asia/Kuala_Lumpur). Locally run
| `php artisan schedule:work`; in production add the cron entry
| `* * * * * cd /path/to/hss && php artisan schedule:run >> /dev/null 2>&1`.
*/

$tz = config('app.timezone', 'Asia/Kuala_Lumpur');

// Day-before reminder to every doctor on call tomorrow (mail + bell notification).
Schedule::command('hss:remind-on-call')
    ->dailyAt('08:00')
    ->timezone($tz)
    ->withoutOverlapping()
    ->description('Remind doctors who are on call tomorrow');

// Hide announcements once their expiry time has passed.
Schedule::command('hss:expire-announcements')
    ->hourly()
    ->timezone($tz)
    ->description('Switch off expired announcements');

// Keep the audit log to a year and read notifications to 90 days.
Schedule::command('hss:prune --audit-days=365 --notification-days=90')
    ->weeklyOn(0, '02:30')
    ->timezone($tz)
    ->description('Prune old audit entries and read notifications');

// Housekeeping for the database queue and password reset tokens.
Schedule::command('queue:prune-failed --hours=168')->dailyAt('03:00')->timezone($tz);
Schedule::command('auth:clear-resets')->dailyAt('03:15')->timezone($tz);
