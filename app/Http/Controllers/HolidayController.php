<?php

namespace App\Http\Controllers;

use App\Models\PublicHoliday;
use App\Services\AuditRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HolidayController extends Controller
{
    public function __construct(private AuditRecorder $audit) {}

    public function index(): View
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        return view('holidays.index', [
            'holidays' => PublicHoliday::query()->orderBy('date')->get(),
            'states' => config('hss.states'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'date' => ['required', 'date'],
            'name' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'in:'.implode(',', config('hss.states'))],
        ]);

        $holiday = PublicHoliday::query()->updateOrCreate(
            ['date' => $data['date'], 'state' => $data['state']],
            ['name' => $data['name']],
        );
        $this->audit->record('holiday.saved', $holiday, $data);

        return back()->with('status', 'Holiday saved.');
    }

    public function destroy(Request $request, PublicHoliday $holiday): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        $this->audit->record('holiday.deleted', $holiday, ['name' => $holiday->name]);
        $holiday->delete();

        return back()->with('status', 'Holiday removed.');
    }
}
