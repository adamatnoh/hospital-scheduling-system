<?php

namespace App\Http\Controllers;

use App\Enums\AssignmentType;
use App\Http\Requests\GenerateScheduleRequest;
use App\Jobs\GenerateScheduleJob;
use App\Models\Department;
use App\Models\ScheduleRun;
use App\Services\AuditRecorder;
use App\Services\Scheduling\SchedulePublisher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GeneratorController extends Controller
{
    public function __construct(private AuditRecorder $audit, private SchedulePublisher $publisher) {}

    public function show(Request $request, string $type): View
    {
        $assignment = AssignmentType::from($type);
        $departments = $request->user()->isAdmin()
            ? Department::query()->orderBy('name')->get()
            : Department::query()->whereKey($request->user()->department_id)->get();

        $departmentId = (int) $request->query('department_id', $departments->first()?->id);
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        $run = ScheduleRun::query()
            ->with('assignments.user', 'assignments.ward')
            ->where('department_id', $departmentId)
            ->where('type', $assignment)
            ->where('year', $year)
            ->where('month', $month)
            ->latest('id')
            ->first();

        return view('roster.generate', [
            'type' => $assignment,
            'departments' => $departments,
            'departmentId' => $departmentId,
            'year' => $year,
            'month' => $month,
            'run' => $run,
        ]);
    }

    public function store(GenerateScheduleRequest $request, Department $department): RedirectResponse
    {
        GenerateScheduleJob::dispatchSync(
            $department->id,
            $request->validated('type'),
            (int) $request->validated('year'),
            (int) $request->validated('month'),
            $request->user()->id,
        );

        $run = ScheduleRun::query()
            ->where('department_id', $department->id)
            ->where('type', $request->validated('type'))
            ->where('year', $request->validated('year'))
            ->where('month', $request->validated('month'))
            ->latest('id')
            ->first();

        $this->audit->record('schedule.previewed', $run, [
            'type' => $request->validated('type'),
            'period' => $request->validated('year').'-'.$request->validated('month'),
        ], $department->id);

        return redirect()->route('generate.show', [
            'type' => $request->validated('type'),
            'department_id' => $department->id,
            'year' => $request->validated('year'),
            'month' => $request->validated('month'),
        ])->with('status', 'Preview ready. Nothing is published until you confirm it.');
    }

    public function publish(Request $request, ScheduleRun $run): RedirectResponse
    {
        $this->authorize('publish', $run);
        $this->publisher->publish($run);
        $this->audit->record('schedule.published', $run, [], $run->department_id);

        return back()->with('status', 'Roster published for the department.');
    }
}
