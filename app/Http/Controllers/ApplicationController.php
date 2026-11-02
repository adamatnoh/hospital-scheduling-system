<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Http\Requests\StoreApplicationRequest;
use App\Http\Requests\StoreSwapRequest;
use App\Models\ScheduleAssignment;
use App\Models\ScheduleRequest;
use App\Services\AuditRecorder;
use App\Services\SwapService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function __construct(private AuditRecorder $audit, private SwapService $swaps) {}

    public function leave(): View
    {
        return view('requests.form', ['type' => RequestType::Leave]);
    }

    public function onCall(): View
    {
        return view('requests.form', ['type' => RequestType::OnCall]);
    }

    public function store(StoreApplicationRequest $request): RedirectResponse
    {
        $application = ScheduleRequest::query()->create([
            'user_id' => $request->user()->id,
            'department_id' => $request->user()->department_id,
            'type' => $request->validated('type'),
            'start_date' => $request->validated('start_date'),
            'end_date' => $request->validated('end_date'),
            'reason' => $request->validated('reason'),
            'status' => RequestStatus::Pending,
        ]);

        $this->audit->record('request.submitted', $application, [], $application->department_id);

        return redirect()->route('calendar.history')->with('status', 'Request sent for review.');
    }

    public function history(Request $request): View
    {
        $applications = ScheduleRequest::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return view('requests.history', ['applications' => $applications]);
    }

    public function swapForm(Request $request): View
    {
        $mine = ScheduleAssignment::query()
            ->with('ward')
            ->where('user_id', $request->user()->id)
            ->where('is_published', true)
            ->whereDate('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->get();

        $others = ScheduleAssignment::query()
            ->with(['user', 'ward'])
            ->where('department_id', $request->user()->department_id)
            ->where('user_id', '!=', $request->user()->id)
            ->where('is_published', true)
            ->whereDate('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->limit(80)
            ->get();

        return view('requests.swap', [
            'mine' => $mine,
            'others' => $others,
        ]);
    }

    public function storeSwap(StoreSwapRequest $request): RedirectResponse
    {
        $this->swaps->request(
            $request->user(),
            (int) $request->validated('assignment_id'),
            (int) $request->validated('counterpart_assignment_id'),
            $request->validated('reason'),
        );

        return redirect()->route('calendar.history')->with('status', 'Swap request sent for review.');
    }
}
