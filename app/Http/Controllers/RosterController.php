<?php

namespace App\Http\Controllers;

use App\Enums\AssignmentType;
use App\Enums\Shift;
use App\Exports\RosterExport;
use App\Http\Requests\UpdateAssignmentRequest;
use App\Models\Department;
use App\Models\ScheduleAssignment;
use App\Models\User;
use App\Services\AuditRecorder;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class RosterController extends Controller
{
    public function __construct(private AuditRecorder $audit) {}

    public function department(Request $request, string $type): View
    {
        $assignment = AssignmentType::from($type);
        $department = $this->selectedDepartment($request);

        return view('roster.department', [
            'type' => $assignment,
            'department' => $department,
            'departments' => $this->departments($request),
            'shifts' => Shift::cases(),
            'doctors' => $department?->doctors ?? collect(),
        ]);
    }

    public function yours(Request $request): View
    {
        return view('roster.yours', [
            'user' => $request->user(),
        ]);
    }

    public function events(Request $request): JsonResponse
    {
        $department = $this->selectedDepartment($request);
        abort_if($department === null, 404);

        $query = ScheduleAssignment::query()
            ->with(['user:id,name', 'ward:id,name,colour'])
            ->where('department_id', $department->id)
            ->whereDate('date', '>=', $request->query('start', now()->startOfMonth()->toDateString()))
            ->whereDate('date', '<=', $request->query('end', now()->endOfMonth()->toDateString()));

        if ($request->query('type') !== 'all') {
            $query->where('type', AssignmentType::from($request->query('type', 'on_call')));
        }

        if ($request->filled('run')) {
            $query->where('schedule_run_id', $request->query('run'));
        } else {
            $query->where('is_published', true);
        }

        if ($request->boolean('mine')) {
            $query->where('user_id', $request->user()->id);
        }

        $actor = $request->user();
        $events = $query->get()->map(function (ScheduleAssignment $assignment) use ($actor) {
            return [
                'id' => $assignment->id,
                'title' => $assignment->label(),
                'start' => $assignment->date->toDateString(),
                'allDay' => true,
                'backgroundColor' => $assignment->shift?->colour() ?? ($assignment->ward?->colour ?? '#012970'),
                'borderColor' => $assignment->shift?->colour() ?? ($assignment->ward?->colour ?? '#012970'),
                'resourceId' => (string) $assignment->user_id,
                'editable' => $actor->can('update', $assignment),
            ];
        });

        return response()->json($events);
    }

    public function resources(Request $request): JsonResponse
    {
        $department = $this->selectedDepartment($request);
        abort_if($department === null, 404);

        $resources = $department->doctors()->orderBy('name')->get()->map(fn (User $doctor) => [
            'id' => (string) $doctor->id,
            'title' => 'Dr '.$doctor->name,
        ]);

        return response()->json($resources);
    }

    public function update(UpdateAssignmentRequest $request, ScheduleAssignment $assignment): JsonResponse
    {
        $userId = (int) $request->input('user_id', $assignment->user_id);
        $date = CarbonImmutable::parse($request->validated('date'))->toDateString();

        $clash = ScheduleAssignment::query()
            ->where('schedule_run_id', $assignment->schedule_run_id)
            ->where('user_id', $userId)
            ->where('type', $assignment->type)
            ->whereDate('date', $date)
            ->whereKeyNot($assignment->id)
            ->exists();

        if ($clash) {
            return response()->json(['message' => 'That doctor already has this duty on that day.'], 422);
        }

        $assignment->update([
            'date' => $date,
            'user_id' => $userId,
            'ward_id' => $request->input('ward_id', $assignment->ward_id),
            'shift' => $request->input('shift', $assignment->shift?->value),
        ]);

        $this->audit->record('assignment.adjusted', $assignment, ['date' => $date], $assignment->department_id);

        return response()->json(['ok' => true]);
    }

    public function export(Request $request, string $type): Response
    {
        $assignment = AssignmentType::from($type);
        $department = $this->selectedDepartment($request);
        abort_if($department === null, 404);

        $month = CarbonImmutable::parse($request->query('month', now()->format('Y-m')).'-01');
        $rows = ScheduleAssignment::query()
            ->with(['user', 'ward', 'department'])
            ->where('department_id', $department->id)
            ->where('type', $assignment)
            ->where('is_published', true)
            ->whereDate('date', '>=', $month->toDateString())
            ->whereDate('date', '<=', $month->endOfMonth()->toDateString())
            ->orderBy('date')
            ->orderBy('user_id')
            ->get();

        $filename = 'hss-'.$department->name.'-'.$assignment->value.'-'.$month->format('Y-m');

        if ($request->query('format') === 'xlsx') {
            return Excel::download(new RosterExport($rows), $filename.'.xlsx');
        }

        $pdf = Pdf::loadView('pdf.roster', [
            'assignments' => $rows,
            'department' => $department,
            'type' => $assignment,
            'month' => $month,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($filename.'.pdf');
    }

    private function selectedDepartment(Request $request): ?Department
    {
        if ($request->user()->isAdmin()) {
            $id = $request->query('department_id') ?? $request->input('department_id');

            return $id
                ? Department::query()->find($id)
                : Department::query()->orderBy('name')->first();
        }

        return $request->user()->department;
    }

    /**
     * @return \Illuminate\Support\Collection<int, Department>
     */
    private function departments(Request $request)
    {
        if ($request->user()->isAdmin()) {
            return Department::query()->orderBy('name')->get();
        }

        return collect([$request->user()->department])->filter();
    }
}
