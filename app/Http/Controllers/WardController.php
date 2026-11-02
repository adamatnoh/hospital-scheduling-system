<?php
// THIS IS CREATE WARD SCHEDULE CONTROLLER
namespace App\Http\Controllers;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DepartmentSchedule;
use App\Models\OnCallApplication;
use App\Models\Assign;
use App\Models\AssignWard;
use App\Models\Wards;

class WardController extends Controller
{
    public function assignWard()
    {
        $userDepartment = Auth::user()->department;
    
        $leaveApplications = DB::table('leave_applications')
            ->select('title', 'user_id', 'department', 'start_date', 'end_date', 'status')
            ->where('department', $userDepartment)
            ->get();
    
        $doctors = DB::table('users')
            ->select('name', 'id', 'department')
            ->where('department', $userDepartment)
            ->get();
    
        $wardLists = DB::table('wards')
            ->select('id', 'name', 'department')
            ->where('department', $userDepartment)
            ->get();
    
        AssignWard::where('department', $userDepartment)->delete();
    
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
    
        $usedDates = [];
    
        $wardIndex = 0; // Track the current ward index
    
        foreach ($doctors as $doctor) {
            $currentDate = $startDate->copy();
    
            while ($currentDate <= $endDate) {
                // Check if the doctor's name appears in leaveApplications
                $foundInLeave = false;
                foreach ($leaveApplications as $leave) {
                    if ($leave->status == 'Yes' && $leave->start_date == $currentDate && $doctor->name == $leave->title) {
                        $foundInLeave = true;
                        break;
                    }
                }
    
                if ($foundInLeave) {
                    // Skip to the next day if the doctor's name was found in leaveApplications with status 'Yes'
                    $currentDate->addDay();
                    continue;
                }
    
                // Assign the ward based on the current ward index
                $ward = $wardLists[$wardIndex];
                $wardIndex = ($wardIndex + 1) % count($wardLists); // Increment the ward index and wrap around if necessary
    
                // Determine the end date for the two-week assignment
                $assignmentEndDate = $currentDate->copy()->addWeeks(2)->subDay();
    
                AssignWard::create([
                    'name' => $doctor->name,
                    'user_id' => $doctor->id,
                    'department' => $doctor->department,
                    'ward' => $ward->name,
                    'ward_id' => $ward->id,
                    'start_date' => $currentDate,
                    'end_date' => $assignmentEndDate,
                ]);
    
                $usedDates[] = $currentDate->toDateString(); // Add the used date to the array
    
                $currentDate = $assignmentEndDate; // Move to the next day after the end of the assignment
            }
        }
    
        $events = array();
        $schedule = AssignWard::where('department', Auth::user()->department)->get();
        $wardColors = array(); // Array to store ward colors
        $i = 1;

        foreach ($schedule as $schedule) {
            $ward = $schedule->ward;
            if (!array_key_exists($ward, $wardColors)) {
                $wardColors[$ward] = '#' . substr(md5($i), 3, 3);
                $i++;
            }

            $events[] = [
                'id' => $schedule->id,
                'title' => 'Dr ' . $schedule->name . PHP_EOL . $ward,
                'start' => $schedule->start_date,
                'end' => $schedule->end_date,
                'allDay' => true,
                'backgroundColor' => $wardColors[$ward] // Assign the ward color
            ];
        }
    
        return view('calendar.create-ward', ['events' => $events]);
    }
    
    private function generateRandomDate($usedDates)
    {
        $date = Carbon::now()->addDays(mt_rand(1, 30));

        while (in_array($date->toDateString(), $usedDates)) {
            $date = Carbon::now()->addDays(mt_rand(1, 30));
        }

        return $date;
    }

    public function ward()
    {
        $events = array();
        $schedule = AssignWard::where('department', Auth::user()->department)->get();
        $wardColors = array(); // Array to store ward colors
        $i = 1;

        foreach ($schedule as $schedule) {
            $ward = $schedule->ward;
            if (!array_key_exists($ward, $wardColors)) {
                $wardColors[$ward] = '#' . substr(md5($i), 3, 3);
                $i++;
            }

            $events[] = [
                'id' => $schedule->id,
                'title' => 'Dr ' . $schedule->name . PHP_EOL . $ward,
                'start' => $schedule->start_date,
                'end' => $schedule->end_date,
                'allDay' => true,
                'backgroundColor' => $wardColors[$ward] // Assign the ward color
            ];
        }

        return view('calendar.create-ward', ['events' => $events]);
    }

    public function viewWard()
    {
        $events = array();
        $schedule = AssignWard::where('department', Auth::user()->department)->get();
        $wardColors = array(); // Array to store ward colors
        $i = 1;

        foreach ($schedule as $schedule) {
            $ward = $schedule->ward;
            if (!array_key_exists($ward, $wardColors)) {
                $wardColors[$ward] = '#' . substr(md5($i), 3, 3);
                $i++;
            }

            $events[] = [
                'id' => $schedule->id,
                'title' => 'Dr ' . $schedule->name . PHP_EOL . $ward,
                'start' => $schedule->start_date,
                'end' => $schedule->end_date,
                'allDay' => true,
                'backgroundColor' => $wardColors[$ward] // Assign the ward color
            ];
        }

        return view('calendar.view-ward', ['events' => $events]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
        ]);
        $schedule = AssignWard::where('department', Auth::user()->department)->find($id);
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
        $schedule = AssignWard::where('department', Auth::user()->department)->find($id);
        if(! $schedule) {
            return response()->json ([
                'error' => 'Unable to locate the event'
            ], 404);
        }
        $schedule->delete();
        return $id;
    }
}