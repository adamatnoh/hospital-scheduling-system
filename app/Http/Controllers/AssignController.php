<?php
// THIS IS CREATE ONCALL SCHEDULE CONTROLLER
namespace App\Http\Controllers;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DepartmentSchedule;
use App\Models\LeaveApplication;
use App\Models\OnCallApplication;
use App\Models\Assign;
use App\Models\User;

class AssignController extends Controller
{
    public function assign()
    {
        $userDepartment = Auth::user()->department;

        $leaveApplications = DB::table('leave_applications')
            ->select('user_id', 'start_date')
            ->where('status', 'Yes')
            ->where('department', $userDepartment)
            ->pluck('user_id')
            ->toArray();

        $onCallApplications = DB::table('on_call_applications')
            ->select('user_id', 'start_date')
            ->where('status', 'Yes')
            ->where('department', $userDepartment)
            ->get();

        $otherDoctors = DB::table('users')
            ->select('name', 'id', 'department')
            ->where('department', $userDepartment)
            ->whereNotIn('id', $leaveApplications)
            ->orWhere(function ($query) use ($onCallApplications) {
                    $query->whereIn('id', $onCallApplications->pluck('user_id')->toArray());
                })
            ->get();
        
        Assign::where('department', $userDepartment)->delete();

        $usedDates = [];
        $oncallDates = [];

        // Assign doctors for the specific dates requested in onCallApplications
        foreach ($onCallApplications as $onCall) {
            $startDate = Carbon::parse($onCall->start_date)->startOfDay();
            $endDate = $startDate->copy();

            Assign::create([
                'title' => User::where('id', $onCall->user_id)->value('name'),
                'user_id' => $onCall->user_id,
                'department' => $userDepartment,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            $usedDates[$startDate->toDateString()][] = $onCall->user_id; // Add the used date and doctor's ID to the array
            $oncallDates[] = $startDate->toDateString();
        }

        // Assign doctors for each day
        $startDate = Carbon::now()->startOfDay();
        $endDate = $startDate->copy()->addDays(30);

        $maxAssignmentsPerMonth = 6;

        while ($startDate <= $endDate) {
            $currentDate = $startDate->toDateString();

            if (!in_array($currentDate, $oncallDates)) {
                // Check if the requested doctor is available for the current date
                $requestedDoctor = $onCallApplications->where('start_date', $currentDate)->first();

                $assignedDoctors = [];

                // If the requested doctor is available, assign the doctor
                if ($requestedDoctor) {
                    $assignedDoctors[] = $requestedDoctor->user_id;
                } else {
                    // Get available doctors for the current date
                    $availableDoctors = $otherDoctors->reject(function ($doctor) use ($leaveApplications, $usedDates, $currentDate, $maxAssignmentsPerMonth) {
                        $doctorAssignments = $usedDates[$currentDate] ?? [];
                        return count($doctorAssignments) >= $maxAssignmentsPerMonth;
                    });

                    while (count($assignedDoctors) < 2 && $availableDoctors->count() > 0) {
                        $randomDoctor = $availableDoctors->random();

                        // Check if the random doctor is already assigned for the current date
                        if (!in_array($randomDoctor->id, $usedDates[$currentDate] ?? [])) {
                            $assignedDoctors[] = $randomDoctor->id;
                        }

                        $availableDoctors = $availableDoctors->reject(function ($doctor) use ($assignedDoctors) {
                            return in_array($doctor->id, $assignedDoctors);
                        });
                    }
                }

                // Assign doctors for the current date
                if (count($assignedDoctors) == 2) {
                    foreach ($assignedDoctors as $doctorId) {
                        Assign::create([
                            'title' => User::where('id', $doctorId)->value('name'),
                            'user_id' => $doctorId,
                            'department' => $userDepartment,
                            'start_date' => $startDate,
                            'end_date' => $startDate,
                        ]);

                        $usedDates[$currentDate][] = $doctorId; // Add the used date and doctor's ID to the array
                    }
                }
            }
            else {
                $requestedDoctor = $onCallApplications->where('start_date', $currentDate)->first();

                $assignedDoctors = [];

                // If the requested doctor is available, assign the doctor
                if ($requestedDoctor) {
                    $assignedDoctors[] = $requestedDoctor->user_id;
                } else {
                    // Get available doctors for the current date
                    $availableDoctors = $otherDoctors->reject(function ($doctor) use ($leaveApplications, $usedDates, $currentDate, $maxAssignmentsPerMonth) {
                        $doctorAssignments = $usedDates[$currentDate] ?? [];
                        return count($doctorAssignments) >= $maxAssignmentsPerMonth;
                    });

                    $randomDoctor = $availableDoctors->random();

                    // Check if the random doctor is already assigned for the current date
                    if (!in_array($randomDoctor->id, $usedDates[$currentDate] ?? [])) {
                        $assignedDoctors[] = $randomDoctor->id;
                    }

                    $availableDoctors = $availableDoctors->reject(function ($doctor) use ($assignedDoctors) {
                        return in_array($doctor->id, $assignedDoctors);
                    });
                }

                Assign::create([
                    'title' => User::where('id', $doctorId)->value('name'),
                    'user_id' => $doctorId,
                    'department' => $userDepartment,
                    'start_date' => $startDate,
                    'end_date' => $startDate,
                ]);

                $usedDates[$currentDate][] = $doctorId; // Add the used date and doctor's ID to the array
            }

            $startDate->addDay();
        }

        $events = [];
        $schedule = Assign::where('department', $userDepartment)->get();
        foreach ($schedule as $schedule) {
            $events[] = [
                'id' => $schedule->id,
                'title' => $schedule->title,
                'start' => $schedule->start_date,
                'end' => $schedule->end_date,
                'allDay' => true
            ];
        }

        return view('calendar.create-oncall', ['events' => $events]);
    }

    private function generateRandomDate($usedDates)
    {
        $date = Carbon::now()->addDays(mt_rand(1, 30));
    
        while (in_array($date->toDateString(), $usedDates)) {
            $date = Carbon::now()->addDays(mt_rand(1, 30));
        }
    
        return $date;
    }
     
    public function oncall()
    {
        $events = array();
        $schedule = Assign::where('department', Auth::user()->department)->get();
        foreach($schedule as $schedule) {
            $events[] = [
                'id' => $schedule->id,
                'title' => 'Dr ' . $schedule->title,
                'start' => $schedule->start_date,
                'end' => $schedule->end_date,
                'allDay' => true
            ];
        }

        return view('calendar.create-oncall', ['events' => $events]);
    }

    public function ViewOnCall()
    {
        $events = array();
        $schedule = Assign::where('department', Auth::user()->department)->get();
        foreach($schedule as $schedule) {
            $events[] = [
                'id' => $schedule->id,
                'title' => 'Dr ' . $schedule->title,
                'start' => $schedule->start_date,
                'end' => $schedule->end_date,
                'allDay' => true
            ];
        }

        return view('calendar.depschedule', ['events' => $events]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
        ]);
        $schedule = Assign::where('department', Auth::user()->department)->find($id);
        if(! $schedule) {
            return response()->json ([
                'error' => 'Unable to locate the event'
            ], 404);
        }
        $schedule->update ([
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
        ]);
        return response()->json ('Event updated');
    }

    public function delete(Request $request, $id)
    {
        $schedule = Assign::where('department', Auth::user()->department)->find($id);
        if(! $schedule) {
            return response()->json ([
                'error' => 'Unable to locate the event'
            ], 404);
        }
        $schedule->delete();
        return $id;
    }
}