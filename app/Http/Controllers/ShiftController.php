<?php
// THIS IS CREATE SHIFT SCHEDULE CONTROLLER
namespace App\Http\Controllers;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DepartmentSchedule;
use App\Models\OnCallApplication;
use App\Models\AssignShift;

class ShiftController extends Controller
{
    public function assignShift()
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

        AssignShift::where('department', $userDepartment)->delete();

        $shifts = ['Morning', 'Evening', 'Night'];
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $usedDates = [];
        $shiftCounts = [
            'Morning' => 0,
            'Evening' => 0,
            'Night' => 0
        ];

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

                // If the doctor's name was not found in leaveApplications with status 'Yes', assign a shift
                if ($currentDate->isWeekday()) {
                    // Determine the shift with the fewest assigned doctors
                    $minShiftCount = min($shiftCounts);
                    $minShifts = array_keys($shiftCounts, $minShiftCount);
                    $shiftIndex = array_rand($minShifts);
                    $shift = $minShifts[$shiftIndex];

                    // Update the shift count
                    $shiftCounts[$shift]++;

                    AssignShift::create([
                        'name' => $doctor->name,
                        'user_id' => $doctor->id,
                        'department' => $doctor->department,
                        'shift' => $shift,
                        'start_date' => $currentDate,
                        'end_date' => $currentDate,
                    ]);

                    $usedDates[] = $currentDate->toDateString(); // Add the used date to the array
                }

                $currentDate->addDay();
            }
        }

        $events = array();
        $schedule = AssignShift::where('department', Auth::user()->department)->get();
        $shiftColors = [
            'Morning' => '#8da399', // Define color for Morning shift
            'Evening' => '#d65302', // Define color for Evening shift
            'Night' => '#311F62' // Define color for Night shift
        ];

        foreach ($schedule as $schedule) {
            $shift = $schedule->shift;

            // Check if the shift has a defined color
            if (array_key_exists($shift, $shiftColors)) {
                $backgroundColor = $shiftColors[$shift];
            } else {
                // If shift color is not defined, use a default color
                $backgroundColor = '#cccccc';
            }

            $events[] = [
                'id' => $schedule->id,
                'title' => 'Dr ' . $schedule->name . PHP_EOL . $shift,
                'start' => $schedule->start_date,
                'end' => $schedule->end_date,
                'allDay' => true,
                'backgroundColor' => $backgroundColor // Assign the shift color
            ];
        }

        return view('calendar.create-shift', ['events' => $events]);
    }
    
    private function generateRandomDate($usedDates)
    {
        $date = Carbon::now()->addDays(mt_rand(1, 30));

        while (in_array($date->toDateString(), $usedDates)) {
            $date = Carbon::now()->addDays(mt_rand(1, 30));
        }

        return $date;
    }

    public function shift()
    {
        $events = array();
        $schedule = AssignShift::where('department', Auth::user()->department)->get();
        $shiftColors = array(
            'Morning' => '#8da399', // Define color for Morning shift
            'Evening' => '#d65302', // Define color for Evening shift
            'Night' => '#311F62' // Define color for Night shift
        );

        foreach ($schedule as $schedule) {
            $shift = $schedule->shift;

            // Check if the shift has a defined color
            if (array_key_exists($shift, $shiftColors)) {
                $backgroundColor = $shiftColors[$shift];
            } else {
                // If shift color is not defined, use a default color
                $backgroundColor = '#cccccc';
            }

            $events[] = [
                'id' => $schedule->id,
                'title' => 'Dr ' . $schedule->name . PHP_EOL . $shift,
                'start' => $schedule->start_date,
                'end' => $schedule->end_date,
                'allDay' => true,
                'backgroundColor' => $backgroundColor // Assign the shift color
            ];
        }

        return view('calendar.create-shift', ['events' => $events]);
    }

    public function ViewShift()
    {
        $events = array();
        $schedule = AssignShift::where('department', Auth::user()->department)->get();
        $shiftColors = array(
            'Morning' => '#8da399', // Define color for Morning shift
            'Evening' => '#d65302', // Define color for Evening shift
            'Night' => '#311F62' // Define color for Night shift
        );

        foreach ($schedule as $schedule) {
            $shift = $schedule->shift;

            // Check if the shift has a defined color
            if (array_key_exists($shift, $shiftColors)) {
                $backgroundColor = $shiftColors[$shift];
            } else {
                // If shift color is not defined, use a default color
                $backgroundColor = '#cccccc';
            }

            $events[] = [
                'id' => $schedule->id,
                'title' => 'Dr ' . $schedule->name . PHP_EOL . $shift,
                'start' => $schedule->start_date,
                'end' => $schedule->end_date,
                'allDay' => true,
                'backgroundColor' => $backgroundColor // Assign the shift color
            ];
        }

        return view('calendar.view-shift', ['events' => $events]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
        ]);
        $schedule = AssignShift::where('department', Auth::user()->department)->find($id);
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
        $schedule = AssignShift::where('department', Auth::user()->department)->find($id);
        if(! $schedule) {
            return response()->json ([
                'error' => 'Unable to locate the event'
            ], 404);
        }
        $schedule->delete();
        return $id;
    }
}