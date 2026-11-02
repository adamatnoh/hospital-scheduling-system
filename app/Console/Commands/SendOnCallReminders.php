<?php

namespace App\Console\Commands;

use App\Enums\AssignmentType;
use App\Models\ScheduleAssignment;
use App\Notifications\OnCallReminder;
use Illuminate\Console\Command;

class SendOnCallReminders extends Command
{
    protected $signature = 'hss:remind-on-call';

    protected $description = 'Email doctors who are on call tomorrow';

    public function handle(): int
    {
        $tomorrow = now()->addDay()->toDateString();
        $assignments = ScheduleAssignment::query()
            ->with(['user', 'department'])
            ->where('type', AssignmentType::OnCall)
            ->where('is_published', true)
            ->whereDate('date', $tomorrow)
            ->get();

        foreach ($assignments as $assignment) {
            $assignment->user?->notify(new OnCallReminder($assignment));
        }

        $this->info('Sent '.$assignments->count().' on-call reminder(s) for '.$tomorrow.'.');

        return self::SUCCESS;
    }
}
