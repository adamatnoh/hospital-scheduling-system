<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Http\Requests\DecideRequest;
use App\Models\ScheduleRequest;
use App\Notifications\RequestDecided;
use App\Services\AuditRecorder;
use App\Services\SwapService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function __construct(private AuditRecorder $audit, private SwapService $swaps) {}

    public function index(Request $request): View
    {
        $applications = $this->scoped($request)
            ->where('status', RequestStatus::Pending)
            ->with(['user', 'counterpart'])
            ->oldest()
            ->get();

        return view('requests.review', ['applications' => $applications]);
    }

    public function history(Request $request): View
    {
        $applications = $this->scoped($request)
            ->where('status', '!=', RequestStatus::Pending)
            ->with(['user', 'reviewer'])
            ->latest('reviewed_at')
            ->get();

        return view('requests.review-history', ['applications' => $applications]);
    }

    public function decide(DecideRequest $request, ScheduleRequest $scheduleRequest): RedirectResponse
    {
        $decision = $request->validated('decision');

        if ($decision === RequestStatus::Approved->value && $scheduleRequest->type === RequestType::Swap) {
            $this->swaps->approve($scheduleRequest, $request->user());
        } else {
            $scheduleRequest->update([
                'status' => $decision,
                'rejection_reason' => $decision === RequestStatus::Rejected->value
                    ? $request->validated('rejection_reason')
                    : null,
                'reviewer_id' => $request->user()->id,
                'reviewed_at' => now(),
            ]);
        }

        $scheduleRequest->refresh()->load('user');
        $scheduleRequest->user->notify(new RequestDecided($scheduleRequest));
        $this->audit->record('request.'.$decision, $scheduleRequest, [
            'rejection_reason' => $scheduleRequest->rejection_reason,
        ], $scheduleRequest->department_id);

        return back()->with('status', 'Request '.$decision.'.');
    }

    private function scoped(Request $request)
    {
        $query = ScheduleRequest::query();
        if (! $request->user()->isAdmin()) {
            $query->where('department_id', $request->user()->department_id);
        }

        return $query;
    }
}
