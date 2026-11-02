<?php
//THIS IS VIEW YOUR SCHEDULE CONTROLLER
namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DepartmentSchedule;
use App\Models\OnCallApplication;
use App\Models\Assign;

class JoinTableController extends Controller
{ 
    public function yourschedule()
    {
        $events = array();
        $userId = Auth::id();

        $onCallSchedule = DB::table('assigns')
            ->select('title', 'start_date', 'end_date')
            ->where('user_id', $userId)
            ->get();

        foreach($onCallSchedule as $schedule) {
            $events[] = [
                'title' => 'On Call', 
                'start' => $schedule->start_date,
                'end' => $schedule->end_date,
                'allDay' => true
            ];
        }
        
        $wardSchedule = DB::table('assign_wards')
            ->select('name', 'start_date', 'end_date', 'ward')
            ->where('user_id', $userId)
            ->get();
        
        foreach($wardSchedule as $schedule) {
            $events[] = [
                'title' => $schedule->ward, 
                'start' => $schedule->start_date,
                'end' => $schedule->end_date,
                'allDay' => true
            ];
        }

        $shiftSchedule = DB::table('assign_shifts')
            ->select('name', 'start_date', 'end_date', 'shift')
            ->where('user_id', $userId)
            ->get(); 

        foreach($shiftSchedule as $schedule) {
            $events[] = [
                'title' => $schedule->shift, 
                'start' => $schedule->start_date,
                'end' => $schedule->end_date,
                'allDay' => true
            ];
        }

        return view('calendar.your-schedule', ['events' => $events]);
    }
}
