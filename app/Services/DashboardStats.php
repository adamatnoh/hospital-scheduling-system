<?php

namespace App\Services;

use App\Enums\AssignmentType;
use App\Enums\RequestStatus;
use App\Models\Department;
use App\Models\ScheduleAssignment;
use App\Models\ScheduleRequest;
use App\Models\User;
use Carbon\CarbonImmutable;

class DashboardStats
{
    /**
     * @return array<string, mixed>
     */
    public function for(User $user): array
    {
        $month = CarbonImmutable::now()->startOfMonth();
        $end = $month->endOfMonth();
        $departmentIds = $user->isAdmin()
            ? Department::query()->pluck('id')
            : collect([$user->department_id])->filter();

        $pending = ScheduleRequest::query()
            ->whereIn('department_id', $departmentIds)
            ->where('status', RequestStatus::Pending)
            ->count();

        $mine = ScheduleRequest::query()
            ->where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        $upcoming = ScheduleAssignment::query()
            ->with(['ward', 'department'])
            ->where('user_id', $user->id)
            ->where('is_published', true)
            ->whereDate('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->limit(6)
            ->get();

        $workload = ScheduleAssignment::query()
            ->with('user:id,name')
            ->whereIn('department_id', $departmentIds)
            ->where('is_published', true)
            ->whereDate('date', '>=', $month->toDateString())
            ->whereDate('date', '<=', $end->toDateString())
            ->get()
            ->groupBy('user_id')
            ->map(fn ($rows) => [
                'name' => 'Dr '.$rows->first()->user->name,
                'duties' => $rows->count(),
            ])
            ->sortByDesc('duties')
            ->values();

        $gaps = [];
        $departments = Department::query()->whereIn('id', $departmentIds)->get();
        foreach ($departments as $department) {
            $counts = ScheduleAssignment::query()
                ->where('department_id', $department->id)
                ->where('type', AssignmentType::OnCall)
                ->where('is_published', true)
                ->whereDate('date', '>=', $month->toDateString())
                ->whereDate('date', '<=', $end->toDateString())
                ->selectRaw('date, count(*) as total')
                ->groupBy('date')
                ->pluck('total', 'date');

            for ($day = $month; $day->lte($end->startOfDay()); $day = $day->addDay()) {
                $key = $day->toDateString();
                $total = (int) ($counts[$key] ?? 0);
                if ($total < $department->doctors_per_on_call_day) {
                    $gaps[] = [
                        'department' => $department->name,
                        'date' => $key,
                        'assigned' => $total,
                        'needed' => $department->doctors_per_on_call_day,
                    ];
                }
            }
        }

        return [
            'pending' => $pending,
            'mine' => $mine,
            'upcoming' => $upcoming,
            'workload' => $workload,
            'gaps' => collect($gaps)->take(8)->all(),
            'gap_count' => count($gaps),
            'departments' => $departments,
        ];
    }
}
