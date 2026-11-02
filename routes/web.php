<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AssignController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\JoinTableController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\WardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Public pages stay open. Every roster, request and staff route requires
| an authenticated user, and mutating actions are limited by role.
|
*/

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::view('/homepage', 'homepage')->name('homepage');

Route::view('/unauthorized', 'unauthorized')->name('unauthorized');

Route::middleware('auth')->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    Route::middleware('role:scheduler,regular')->group(function () {
        Route::get('/department-oncall', [AssignController::class, 'viewOnCall'])->name('calendar.depschedule');
        Route::get('/department-ward', [WardController::class, 'viewWard'])->name('calendar.view-ward');
        Route::get('/department-shift', [ShiftController::class, 'viewShift'])->name('calendar.view-shift');
        Route::get('/your-schedule', [JoinTableController::class, 'yourschedule'])->name('calendar.yourschedule');

        Route::get('/leave', [CalendarController::class, 'leave'])->name('calendar.leave');
        Route::post('/leave', [CalendarController::class, 'storel'])->name('calendar.storeLeave');
        Route::get('/oncall', [CalendarController::class, 'oncall'])->name('calendar.oncall');
        Route::post('/oncall', [CalendarController::class, 'storeo'])->name('calendar.storeOnCall');
        Route::get('/history', [CalendarController::class, 'history'])->name('calendar.history');
    });

    Route::middleware('role:scheduler')->group(function () {
        Route::get('/assign', [AssignController::class, 'assign'])->name('assign');
        Route::get('/create-oncall', [AssignController::class, 'oncall'])->name('create-oncall');
        Route::patch('/update-oncall/{id}', [AssignController::class, 'update'])->name('assign.update');
        Route::delete('/delete-oncall/{id}', [AssignController::class, 'delete'])->name('assign.delete');

        Route::get('/assign-ward', [WardController::class, 'assignWard'])->name('assign-ward');
        Route::get('/create-ward', [WardController::class, 'ward'])->name('create-ward');
        Route::patch('/update-ward/{id}', [WardController::class, 'update'])->name('ward.update');
        Route::delete('/delete-ward/{id}', [WardController::class, 'delete'])->name('ward.delete');

        Route::get('/assign-shift', [ShiftController::class, 'assignShift'])->name('assign-shift');
        Route::get('/create-shift', [ShiftController::class, 'shift'])->name('create-shift');
        Route::patch('/update-shift/{id}', [ShiftController::class, 'update'])->name('shift.update');
        Route::delete('/delete-shift/{id}', [ShiftController::class, 'delete'])->name('shift.delete');

        Route::get('/review', [CalendarController::class, 'review'])->name('review');
        Route::get('/review-history', [CalendarController::class, 'reviewHistory'])->name('reviewHistory');
        Route::patch('/review/update-leave/{id}', [CalendarController::class, 'updateReviewLeave'])->name('updateReviewLeave');
        Route::patch('/review/update-oncall/{id}', [CalendarController::class, 'updateReviewOnCall'])->name('updateReviewOnCall');
        Route::patch('/review/update-status-leave/{id}', [CalendarController::class, 'updateStatusLeave'])->name('updateStatusLeave');
        Route::patch('/review/update-status-oncall/{id}', [CalendarController::class, 'updateStatusOnCall'])->name('updateStatusOnCall');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/staff', [AdminController::class, 'manageStaff'])->name('manageStaff');
        Route::post('/staff/update', [AdminController::class, 'updateStaff'])->name('updateStaff');
        Route::get('/getStaff/{id}', [AdminController::class, 'getStaff'])->name('getStaff');
        Route::view('/add-staff', 'addStaff')->name('staff-form');
        Route::post('/adding', [AdminController::class, 'addStaff'])->name('addStaff');
        Route::delete('/deleteStaff/{id}', [AdminController::class, 'deleteStaff'])->name('deleteStaff');
    });
});
