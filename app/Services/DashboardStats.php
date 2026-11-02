<?php

namespace App\Services;

use App\Enums\AssignmentType;
use App\Enums\RequestStatus;
use App\Enums\Role;
use App\Models\Department;
use App\Models\ScheduleAssignment;
use App\Models\ScheduleRequest;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DashboardStats
{
    /**
     * @return array<string, mixed>
     */
    public function for(User $user): array
    {
        $today = CarbonImmutable::now()->startOfDay();
        $month = $today->startOfMonth();
        $end = $month->endOfMonth();
        $departmentIds = $user->isAdmin()
            ? Department::query()->pluck('id')
            : collect([$user->department_id])->filter();

        $pendingQuery = ScheduleRequest::query()
            ->whereIn('department_id', $departmentIds)
            ->where('status', RequestStatus::Pending);

        $pending = (clone $pendingQuery)->count();
        $pendingList = (clone $pendingQuery)->with(['user', 'counterpart'])->oldest()->limit(5)->get();

        $mine = ScheduleRequest::query()
            ->where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        $upcoming = ScheduleAssignment::query()
            ->with(['ward', 'department'])
            ->where('user_id', $user->id)
            ->where('is_published', true)
            ->whereDate('date', '>=', $today->toDateString())
            ->orderBy('date')
            ->limit(6)
            ->get();

        // Admins have no duties of their own, so their timeline shows the hospital's next on-call days.
        $timeline = $user->isAdmin()
            ? ScheduleAssignment::query()
                ->with(['user', 'department'])
                ->whereIn('department_id', $departmentIds)
                ->where('type', AssignmentType::OnCall)
                ->where('is_published', true)
                ->whereDate('date', '>=', $today->toDateString())
                ->orderBy('date')
                ->limit(8)
                ->get()
            : $upcoming;

        $monthRows = $this->published($departmentIds, $month, $end)->with('user:id,name')->get();
        $previous = $this->published($departmentIds, $month->subMonth(), $month->subMonth()->endOfMonth())->count();

        $workload = $monthRows
            ->groupBy('user_id')
            ->map(fn ($rows) => [
                'name' => 'Dr '.$rows->first()->user->name,
                'duties' => $rows->count(),
                'on_call' => $rows->where('type', AssignmentType::OnCall)->count(),
                'ward' => $rows->where('type', AssignmentType::Ward)->count(),
                'shift' => $rows->where('type', AssignmentType::Shift)->count(),
            ])
            ->sortByDesc('duties')
            ->values();

        $onDutyToday = $monthRows
            ->filter(fn (ScheduleAssignment $row) => $row->date->isSameDay($today))
            ->pluck('user_id')
            ->unique()
            ->count();

        $gaps = [];
        $departments = Department::query()->whereIn('id', $departmentIds)->get();
        foreach ($departments as $department) {
            $counts = $monthRows
                ->where('department_id', $department->id)
                ->where('type', AssignmentType::OnCall)
                ->countBy(fn (ScheduleAssignment $row) => $row->date->toDateString());

            for ($day = $month; $day->lte($end->startOfDay()); $day = $day->addDay()) {
                $key = $day->toDateString();
                $total = (int) ($counts[$key] ?? 0);
                if ($total < $department->doctors_per_on_call_day) {
                    $gaps[] = [
                        'department' => $department->name,
                        'date' => $key,
                        'assigned' => $total,
                        'needed' => $department->doctors_per_on_call_day,
                        'severity' => $total === 0 ? 'critical' : 'warning',
                        'upcoming' => $key >= $today->toDateString(),
                    ];
                }
            }
        }

        $gaps = collect($gaps)->sortBy([['upcoming', 'desc'], ['date', 'asc']])->values();

        return [
            'pending' => $pending,
            'pending_list' => $pendingList,
            'mine' => $mine,
            'upcoming' => $upcoming,
            'timeline' => $timeline,
            'workload' => $workload,
            'gaps' => $gaps->take(8)->all(),
            'gap_count' => $gaps->count(),
            'gap_critical' => $gaps->where('severity', 'critical')->count(),
            'gap_upcoming' => $gaps->where('upcoming', true)->count(),
            'departments' => $departments,
            'duties_month' => $monthRows->count(),
            'duties_previous' => $previous,
            'on_duty_today' => $onDutyToday,
            'staff_count' => User::query()->whereIn('department_id', $departmentIds)->where('role', '!=', Role::Admin)->count(),
        ];
    }

    /**
     * @param  Collection<int, mixed>  $departmentIds
     * @return Builder<ScheduleAssignment>
     */
    private function published($departmentIds, CarbonImmutable $from, CarbonImmutable $to)
    {
        return ScheduleAssignment::query()
            ->whereIn('department_id', $departmentIds)
            ->where('is_published', true)
            ->whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString());
    }
}
