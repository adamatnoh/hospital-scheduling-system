<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DepartmentSchedule;
use App\Models\LeaveApplication;
use App\Models\OnCallApplication;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function leave()
    {
        return view('calendar.leave');
    }

    public function oncall()
    {
        return view('calendar.oncall');
    }

    public function depschedule()
    {
        $events = array();
        $schedule = DepartmentSchedule::all();
        foreach($schedule as $schedule) {
            $events[] = [
                'title' => $schedule->title,
                'start' => $schedule->start_date,
                'end' => $schedule->end_date,
                'allDay' => true
            ];
        }

        return view('calendar.depschedule', ['events' => $events]);
    }

    public function storel (Request $request)
    {
        $data = $this->validatedApplication($request);

        $schedule = LeaveApplication::create([
            'title' => $request->user()->name,
            'user_id' => $request->user()->id,
            'department' => $request->user()->department,
            'reason' => $data['reason'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => 'Pending',
            'rejection' => '',
        ]);

        return response()->json($schedule);
    }

    public function storeo (Request $request)
    {
        $data = $this->validatedApplication($request);

        $schedule = OnCallApplication::create([
            'title' => $request->user()->name,
            'user_id' => $request->user()->id,
            'department' => $request->user()->department,
            'reason' => $data['reason'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => 'Pending',
            'rejection' => '',
        ]);

        return response()->json($schedule);
    }

    private function validatedApplication(Request $request): array
    {
        return $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);
    }

    public function history (Request $request)
    {
        $leaves = LeaveApplication::select('id', 'title', 'department', 'start_date', 'end_date', 'reason', 'status', 'rejection')
            ->where('user_id', auth()->user()->id)
            ->get();

        $onCalls = OnCallApplication::select('id', 'title', 'department', 'start_date', 'end_date', 'reason', 'status', 'rejection')
            ->where('user_id', auth()->user()->id)
            ->get();

        $leaves = $leaves->map(function ($leave) {
            $leave['application_type'] = 'Leave';
            $leave['start_date'] = Carbon::parse($leave['start_date'])->format('d-m');
            $leave['end_date'] = Carbon::parse($leave['end_date'])->format('d-m');
            return $leave;
        });

        $onCalls = $onCalls->map(function ($onCall) {
            $onCall['application_type'] = 'On Call';
            $onCall['start_date'] = Carbon::parse($onCall['start_date'])->format('d-m');
            $onCall['end_date'] = Carbon::parse($onCall['end_date'])->format('d-m');
            return $onCall;
        });

        $applications = $leaves->concat($onCalls);

        return view('calendar.history', compact('applications'));
    }

    public function review (Request $request)
    {
        $department = $request->user()->department;

        $leaves = LeaveApplication::select('id', 'start_date', 'end_date', 'reason', 'status', 'title', 'rejection')
            ->where('status', 'Pending')
            ->where('department', $department)
            ->get();

        $onCalls = OnCallApplication::select('id', 'start_date', 'end_date', 'reason', 'status', 'title', 'rejection')
            ->where('status', 'Pending')
            ->where('department', $department)
            ->get();

        $leaves = $leaves->map(function ($leave) {
            $leave['application_type'] = 'Leave';
            $leave['start_date'] = Carbon::parse($leave['start_date'])->format('d-m');
            $leave['end_date'] = Carbon::parse($leave['end_date'])->format('d-m');
            return $leave;
        });

        $onCalls = $onCalls->map(function ($onCall) {
            $onCall['application_type'] = 'On Call';
            $onCall['start_date'] = Carbon::parse($onCall['start_date'])->format('d-m');
            $onCall['end_date'] = Carbon::parse($onCall['end_date'])->format('d-m');
            return $onCall;
        });

        $applications = $leaves->concat($onCalls);

        return view('review', compact('applications'));
    }

    public function reviewHistory (Request $request)
    {
        $department = $request->user()->department;

        $leaves = LeaveApplication::select('id', 'title', 'department', 'start_date', 'end_date', 'reason', 'status', 'rejection')
            ->where('status', '!=', 'Pending')
            ->where('department', $department)
            ->get();

        $onCalls = OnCallApplication::select('id', 'title', 'department', 'start_date', 'end_date', 'reason', 'status', 'rejection')
            ->where('status', '!=', 'Pending')
            ->where('department', $department)
            ->get();

        $leaves = $leaves->map(function ($leave) {
            $leave['application_type'] = 'Leave';
            $leave['start_date'] = Carbon::parse($leave['start_date'])->format('d-m');
            $leave['end_date'] = Carbon::parse($leave['end_date'])->format('d-m');
            return $leave;
        });

        $onCalls = $onCalls->map(function ($onCall) {
            $onCall['application_type'] = 'On Call';
            $onCall['start_date'] = Carbon::parse($onCall['start_date'])->format('d-m');
            $onCall['end_date'] = Carbon::parse($onCall['end_date'])->format('d-m');
            return $onCall;
        });

        $applications = $leaves->concat($onCalls);

        return view('review-history', compact('applications'));
    }

    public function updateReviewLeave (Request $request, $id)
    {
        return $this->decide($request, LeaveApplication::class, $id, true);
    }

    public function updateReviewOnCall (Request $request, $id)
    {
        return $this->decide($request, OnCallApplication::class, $id, true);
    }

    public function updateStatusLeave (Request $request, $id)
    {
        return $this->decide($request, LeaveApplication::class, $id, false);
    }

    public function updateStatusOnCall (Request $request, $id)
    {
        return $this->decide($request, OnCallApplication::class, $id, false);
    }

    private function decide(Request $request, string $model, $id, bool $withReason)
    {
        $rules = ['status' => ['required', 'in:Yes,No']];
        if ($withReason) {
            $rules['rejection'] = ['required_if:status,No', 'nullable', 'string', 'max:1000'];
        }
        $data = $request->validate($rules);

        $schedule = $model::query()
            ->where('department', $request->user()->department)
            ->find($id);

        if (! $schedule) {
            return response()->json([
                'error' => 'Unable to locate the request',
            ], 404);
        }

        $schedule->update([
            'status' => $data['status'],
            'rejection' => $data['status'] === 'No' ? ($data['rejection'] ?? '') : '',
        ]);

        return response()->json($schedule);
    }
}