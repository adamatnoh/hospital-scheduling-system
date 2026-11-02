<?php

namespace App\Services\Scheduling;

use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Models\Department;
use App\Models\PublicHoliday;
use App\Models\ScheduleRequest;
use Carbon\CarbonImmutable;

class RosterContextFactory
{
    public function make(Department $department, int $year, int $month): RosterContext
    {
        $start = CarbonImmutable::create($year, $month, 1, 0, 0, 0, config('app.timezone'));
        $end = $start->endOfMonth()->startOfDay();

        $doctors = $department->doctors()->reorder()->orderBy('id')->get();

        $leave = [];
        $onCall = [];

        $requests = ScheduleRequest::query()
            ->where('department_id', $department->id)
            ->where('status', RequestStatus::Approved)
            ->whereDate('end_date', '>=', $start->toDateString())
            ->whereDate('start_date', '<=', $end->toDateString())
            ->orderBy('id')
            ->get();

        foreach ($requests as $request) {
            $cursor = CarbonImmutable::parse($request->start_date)->max($start)->startOfDay();
            $last = CarbonImmutable::parse($request->end_date)->min($end)->startOfDay();

            for ($day = $cursor; $day->lte($last); $day = $day->addDay()) {
                $key = $day->toDateString();
                if ($request->type === RequestType::Leave) {
                    $leave[$request->user_id][$key] = true;
                }
                if ($request->type === RequestType::OnCall) {
                    $onCall[$key][] = $request->user_id;
                }
            }
        }

        $holidays = [];
        $states = array_values(array_unique(array_filter(['National', $department->holiday_state])));
        $rows = PublicHoliday::query()
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', $end->toDateString())
            ->whereIn('state', $states)
            ->orderBy('id')
            ->get();

        foreach ($rows as $holiday) {
            $holidays[$holiday->date->toDateString()] = $holiday->name;
        }

        return new RosterContext(
            $department,
            $year,
            $month,
            $start,
            $end,
            $doctors,
            $leave,
            $onCall,
            $holidays,
        );
    }
}
