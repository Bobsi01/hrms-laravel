<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Dashboard\DashboardController;

/*
|--------------------------------------------------------------------------
| Public Routes (Guest)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Unauthorized page
    Route::get('/unauthorized', fn () => view('errors.unauthorized'))->name('unauthorized');

    /*
    |----------------------------------------------------------------------
    | HR Core Module Routes
    |----------------------------------------------------------------------
    */

    // Employees
    Route::prefix('employees')->name('employees.')->middleware('module.access:hr_core,employees,read')->group(function () {
        Route::get('/', [\App\Http\Controllers\Employees\EmployeeController::class, 'index'])->name('index');
        // create, edit, view, etc. will be added as controllers are built
    });

    // Departments
    Route::prefix('departments')->name('departments.')->middleware('module.access:hr_core,departments,read')->group(function () {
        Route::get('/', [\App\Http\Controllers\Departments\DepartmentController::class, 'index'])->name('index');
    });

    // Positions
    Route::prefix('positions')->name('positions.')->middleware('module.access:hr_core,positions,read')->group(function () {
        Route::get('/', [\App\Http\Controllers\Positions\PositionController::class, 'index'])->name('index');
    });

    /*
    |----------------------------------------------------------------------
    | Leave Module Routes
    |----------------------------------------------------------------------
    */
    Route::prefix('leave')->name('leave.')->group(function () {
        // Employee self-service (self-service permission, no module check needed)
        Route::get('/', [\App\Http\Controllers\Leave\LeaveController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Leave\LeaveController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Leave\LeaveController::class, 'store'])->name('store');

        // Admin approval dashboard
        Route::get('/admin', [\App\Http\Controllers\Leave\LeaveController::class, 'admin'])
            ->middleware('module.access:leave,leave_admin,read')
            ->name('admin');
    });

    /*
    |----------------------------------------------------------------------
    | Attendance Module Routes
    |----------------------------------------------------------------------
    */
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/my', [\App\Http\Controllers\Attendance\AttendanceController::class, 'my'])->name('my');
        Route::get('/', [\App\Http\Controllers\Attendance\AttendanceController::class, 'index'])
            ->middleware('module.access:attendance,attendance_admin,read')
            ->name('index');
    });

    /*
    |----------------------------------------------------------------------
    | Payroll Module Routes
    |----------------------------------------------------------------------
    */
    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/my-payslips', [\App\Http\Controllers\Payroll\PayrollController::class, 'myPayslips'])->name('my-payslips');
        Route::get('/', [\App\Http\Controllers\Payroll\PayrollController::class, 'index'])
            ->middleware('module.access:payroll,payroll_runs,read')
            ->name('index');
    });

    /*
    |----------------------------------------------------------------------
    | User Account & Admin Routes
    |----------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->middleware('module.access:system,system_settings,read')->group(function () {
        Route::get('/', fn () => view('admin.index'))->name('index');
    });

    /*
    |----------------------------------------------------------------------
    | Notifications
    |----------------------------------------------------------------------
    */
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Notifications\NotificationController::class, 'index'])->name('index');
        Route::get('/feed', [\App\Http\Controllers\Notifications\NotificationController::class, 'feed'])->name('feed');
        Route::post('/mark-all-read', [\App\Http\Controllers\Notifications\NotificationController::class, 'markAllRead'])->name('markAllRead');
        Route::post('/{notification}/mark-read', [\App\Http\Controllers\Notifications\NotificationController::class, 'markRead'])->name('markRead');
    });
});
