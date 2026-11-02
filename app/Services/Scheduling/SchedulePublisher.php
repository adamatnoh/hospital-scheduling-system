<?php

namespace App\Services\Scheduling;

use App\Enums\RunStatus;
use App\Models\ScheduleAssignment;
use App\Models\ScheduleRun;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class SchedulePublisher
{
    public function publish(ScheduleRun $run): ScheduleRun
    {
        return DB::transaction(function () use ($run) {
            $start = CarbonImmutable::create($run->year, $run->month, 1)->startOfMonth();
            $end = $start->endOfMonth();

            ScheduleAssignment::query()
                ->where('department_id', $run->department_id)
                ->where('type', $run->type)
                ->where('is_published', true)
                ->whereDate('date', '>=', $start->toDateString())
                ->whereDate('date', '<=', $end->toDateString())
                ->delete();

            ScheduleRun::query()
                ->where('department_id', $run->department_id)
                ->where('type', $run->type)
                ->where('year', $run->year)
                ->where('month', $run->month)
                ->where('status', RunStatus::Published)
                ->whereKeyNot($run->id)
                ->delete();

            $run->assignments()->update(['is_published' => true]);
            $run->update([
                'status' => RunStatus::Published,
                'published_at' => now(),
            ]);

            return $run->refresh();
        });
    }
}
