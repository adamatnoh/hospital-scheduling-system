<?php

namespace App\Console\Commands;

use App\Enums\AssignmentType;
use App\Models\ScheduleAssignment;
use App\Notifications\OnCallReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendOnCallReminders extends Command
{
    protected $signature = 'hss:remind-on-call {--date= : Duty date to remind about (Y-m-d). Defaults to tomorrow.}';

    protected $description = 'Email doctors who are on call tomorrow';

    public function handle(): int
    {
        $day = $this->option('date')
            ? Carbon::parse((string) $this->option('date'))->toDateString()
            : now()->addDay()->toDateString();

        $assignments = ScheduleAssignment::query()
            ->with(['user', 'department'])
            ->where('type', AssignmentType::OnCall)
            ->where('is_published', true)
            ->whereDate('date', $day)
            ->get();

        foreach ($assignments as $assignment) {
            $assignment->user?->notify(new OnCallReminder($assignment));
        }

        $this->info('Sent '.$assignments->count().' on-call reminder(s) for '.$day.'.');

        return self::SUCCESS;
    }
}
