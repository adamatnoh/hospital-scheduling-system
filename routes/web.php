<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GeneratorController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\IcalController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RosterController;
use App\Http\Controllers\StaffController;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Department;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('landing', [
        'announcements' => Announcement::query()->visible()->get(),
    ]);
})->name('landing');

Route::view('/unauthorized', 'unauthorized')->name('unauthorized');

Route::get('/ical/{token}', [IcalController::class, 'show'])->name('ical.feed');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/department/{type}', [RosterController::class, 'department'])->name('calendar.department');
    Route::get('/department-oncall', fn () => redirect()->route('calendar.department', 'on_call'))->name('calendar.depschedule');
    Route::get('/department-ward', fn () => redirect()->route('calendar.department', 'ward'))->name('calendar.view-ward');
    Route::get('/department-shift', fn () => redirect()->route('calendar.department', 'shift'))->name('calendar.view-shift');
    Route::get('/your-schedule', [RosterController::class, 'yours'])->name('calendar.yourschedule');
    Route::get('/calendar/events', [RosterController::class, 'events'])->name('calendar.events');
    Route::get('/calendar/resources', [RosterController::class, 'resources'])->name('calendar.resources');
    Route::patch('/assignments/{assignment}', [RosterController::class, 'update'])->name('assignments.update');
    Route::get('/schedules/{type}/export', [RosterController::class, 'export'])->name('schedules.export');
    Route::get('/ical', [IcalController::class, 'download'])->name('ical.download');

    Route::middleware('role:scheduler,regular,admin')->group(function () {
        Route::get('/leave', [ApplicationController::class, 'leave'])->name('calendar.leave');
        Route::post('/leave', [ApplicationController::class, 'store'])->name('calendar.storeLeave');
        Route::get('/oncall', [ApplicationController::class, 'onCall'])->name('calendar.oncall');
        Route::post('/oncall', [ApplicationController::class, 'store'])->name('calendar.storeOnCall');
        Route::get('/history', [ApplicationController::class, 'history'])->name('calendar.history');
        Route::get('/swaps', [ApplicationController::class, 'swapForm'])->name('swaps.create');
        Route::post('/swaps', [ApplicationController::class, 'storeSwap'])->name('swaps.store');
    });

    Route::middleware('role:scheduler,admin')->group(function () {
        Route::get('/generate/{type}', [GeneratorController::class, 'show'])->name('generate.show');
        Route::get('/create-oncall', fn () => redirect()->route('generate.show', 'on_call'))->name('create-oncall');
        Route::get('/create-ward', fn () => redirect()->route('generate.show', 'ward'))->name('create-ward');
        Route::get('/create-shift', fn () => redirect()->route('generate.show', 'shift'))->name('create-shift');
        Route::post('/generate/{department}', [GeneratorController::class, 'store'])->name('generate.store');
        Route::post('/runs/{run}/publish', [GeneratorController::class, 'publish'])->name('runs.publish');

        Route::get('/review', [ReviewController::class, 'index'])->name('review');
        Route::get('/review-history', [ReviewController::class, 'history'])->name('reviewHistory');
        Route::post('/review/{scheduleRequest}', [ReviewController::class, 'decide'])->name('review.decide');

        Route::get('/rules/{department}', function (Department $department) {
            return view('roster.rules', ['department' => $department]);
        })->name('rules.show');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/staff', [StaffController::class, 'index'])->name('manageStaff');
        Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
        Route::put('/staff/{user}', [StaffController::class, 'update'])->name('staff.update');
        Route::delete('/staff/{user}', [StaffController::class, 'destroy'])->name('staff.destroy');

        Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index');
        Route::post('/holidays', [HolidayController::class, 'store'])->name('holidays.store');
        Route::delete('/holidays/{holiday}', [HolidayController::class, 'destroy'])->name('holidays.destroy');

        Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('/announcements/create', [AnnouncementController::class, 'create'])->name('announcements.create');
        Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
        Route::get('/announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('announcements.edit');
        Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
        Route::patch('/announcements/{announcement}/toggle', [AnnouncementController::class, 'toggle'])->name('announcements.toggle');
        Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');

        Route::get('/audit', function () {
            return view('audit.index', [
                'logs' => AuditLog::query()->with('user')->latest('id')->limit(150)->get(),
            ]);
        })->name('audit.index');
    });
});
