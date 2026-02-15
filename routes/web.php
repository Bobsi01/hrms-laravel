<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Employees\EmployeeController;
use App\Http\Controllers\Departments\DepartmentController;
use App\Http\Controllers\Positions\PositionController;
use App\Http\Controllers\Leave\LeaveController;
use App\Http\Controllers\Attendance\AttendanceController;
use App\Http\Controllers\Payroll\PayrollController;
use App\Http\Controllers\Overtime\OvertimeController;
use App\Http\Controllers\Memos\MemoController;
use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Audit\AuditController;
use App\Http\Controllers\Notifications\NotificationController;
use App\Http\Controllers\Documents\DocumentController;
use App\Http\Controllers\Recruitment\RecruitmentController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\CutoffPeriodController;
use App\Http\Controllers\Admin\LeaveSettingsController;
use App\Http\Controllers\Admin\BirReportController;
use App\Http\Controllers\Admin\PayrollConfigController;
use App\Http\Controllers\Compliance\DataCorrectionController;
use App\Http\Controllers\Compliance\PrivacyConsentController;
use App\Http\Controllers\Admin\WorkScheduleController;
use App\Http\Controllers\Admin\ApprovalWorkflowController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Inventory\InventoryController;
use App\Http\Controllers\Inventory\CategoryController;
use App\Http\Controllers\Inventory\SupplierController;
use App\Http\Controllers\Inventory\LocationController;
use App\Http\Controllers\Inventory\PosController;

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

    // Employees — full CRUD
    Route::prefix('employees')->name('employees.')->middleware('module.access:hr_core,employees,read')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('index');
        Route::get('/create', [EmployeeController::class, 'create'])->name('create');
        Route::post('/', [EmployeeController::class, 'store'])->name('store');
        Route::get('/{employee}', [EmployeeController::class, 'show'])->name('show');
        Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->name('edit');
        Route::put('/{employee}', [EmployeeController::class, 'update'])->name('update');
        Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('destroy');
    });

    // Departments — full CRUD + supervisor management
    Route::prefix('departments')->name('departments.')->middleware('module.access:hr_core,departments,read')->group(function () {
        Route::get('/', [DepartmentController::class, 'index'])->name('index');
        Route::get('/create', [DepartmentController::class, 'create'])->name('create');
        Route::post('/', [DepartmentController::class, 'store'])->name('store');
        Route::get('/{department}/edit', [DepartmentController::class, 'edit'])->name('edit');
        Route::put('/{department}', [DepartmentController::class, 'update'])->name('update');
        Route::delete('/{department}', [DepartmentController::class, 'destroy'])->name('destroy');

        // Supervisor management
        Route::get('/{department}/supervisors', [DepartmentController::class, 'supervisors'])->name('supervisors');
        Route::post('/{department}/supervisors', [DepartmentController::class, 'addSupervisor'])->name('supervisors.add');
        Route::delete('/{department}/supervisors/{supervisor}', [DepartmentController::class, 'removeSupervisor'])->name('supervisors.remove');
    });

    // Positions — full CRUD + permission management
    Route::prefix('positions')->name('positions.')->middleware('module.access:hr_core,positions,read')->group(function () {
        Route::get('/', [PositionController::class, 'index'])->name('index');
        Route::get('/create', [PositionController::class, 'create'])->name('create');
        Route::post('/', [PositionController::class, 'store'])->name('store');
        Route::get('/{position}/edit', [PositionController::class, 'edit'])->name('edit');
        Route::put('/{position}', [PositionController::class, 'update'])->name('update');
        Route::delete('/{position}', [PositionController::class, 'destroy'])->name('destroy');

        // Access permission management
        Route::get('/{position}/permissions', [PositionController::class, 'permissions'])->name('permissions');
        Route::put('/{position}/permissions', [PositionController::class, 'updatePermissions'])->name('permissions.update');
    });

    /*
    |----------------------------------------------------------------------
    | Leave Module Routes
    |----------------------------------------------------------------------
    */
    Route::prefix('leave')->name('leave.')->group(function () {
        // Employee self-service (self-service permission, no module check needed)
        Route::get('/', [LeaveController::class, 'index'])->name('index');
        Route::get('/create', [LeaveController::class, 'create'])->name('create');
        Route::post('/', [LeaveController::class, 'store'])->name('store');

        // Admin approval dashboard (must come before {leave} wildcard)
        Route::get('/admin', [LeaveController::class, 'admin'])
            ->middleware('module.access:leave,leave_admin,read')
            ->name('admin');

        // Leave detail & actions
        Route::get('/{leave}', [LeaveController::class, 'show'])->name('show');
        Route::post('/{leave}/approve', [LeaveController::class, 'approve'])
            ->middleware('module.access:leave,leave_admin,write')
            ->name('approve');
        Route::post('/{leave}/reject', [LeaveController::class, 'reject'])
            ->middleware('module.access:leave,leave_admin,write')
            ->name('reject');
    });

    /*
    |----------------------------------------------------------------------
    | Overtime Module Routes
    |----------------------------------------------------------------------
    */
    Route::prefix('overtime')->name('overtime.')->group(function () {
        // Employee self-service
        Route::get('/', [OvertimeController::class, 'index'])->name('index');
        Route::get('/create', [OvertimeController::class, 'create'])->name('create');
        Route::post('/', [OvertimeController::class, 'store'])->name('store');

        // Admin management (must come before {overtime} wildcard)
        Route::get('/admin', [OvertimeController::class, 'admin'])
            ->middleware('module.access:payroll,overtime,read')
            ->name('admin');

        // Approve/reject actions
        Route::post('/{overtime}/approve', [OvertimeController::class, 'approve'])
            ->middleware('module.access:payroll,overtime,write')
            ->name('approve');
        Route::post('/{overtime}/reject', [OvertimeController::class, 'reject'])
            ->middleware('module.access:payroll,overtime,write')
            ->name('reject');
    });

    /*
    |----------------------------------------------------------------------
    | Attendance Module Routes
    |----------------------------------------------------------------------
    */
    Route::prefix('attendance')->name('attendance.')->group(function () {
        // Employee self-service
        Route::get('/my', [AttendanceController::class, 'my'])->name('my');

        // Admin routes
        Route::get('/', [AttendanceController::class, 'index'])
            ->middleware('module.access:attendance,attendance_admin,read')
            ->name('index');
        Route::get('/create', [AttendanceController::class, 'create'])
            ->middleware('module.access:attendance,attendance_admin,write')
            ->name('create');
        Route::post('/', [AttendanceController::class, 'store'])
            ->middleware('module.access:attendance,attendance_admin,write')
            ->name('store');
        Route::get('/import', [AttendanceController::class, 'import'])
            ->middleware('module.access:attendance,attendance_admin,write')
            ->name('import');
        Route::post('/import', [AttendanceController::class, 'processImport'])
            ->middleware('module.access:attendance,attendance_admin,write')
            ->name('process-import');
    });

    /*
    |----------------------------------------------------------------------
    | Payroll Module Routes
    |----------------------------------------------------------------------
    */
    Route::prefix('payroll')->name('payroll.')->group(function () {
        // Employee self-service
        Route::get('/my-payslips', [PayrollController::class, 'myPayslips'])->name('my-payslips');
        Route::post('/file-complaint', [PayrollController::class, 'fileComplaint'])->name('file-complaint');
        Route::get('/payslip/{payslip}', [PayrollController::class, 'payslipShow'])->name('payslip');

        // Admin routes
        Route::get('/', [PayrollController::class, 'index'])
            ->middleware('module.access:payroll,payroll_runs,read')
            ->name('index');
        Route::get('/create', [PayrollController::class, 'create'])
            ->middleware('module.access:payroll,payroll_runs,write')
            ->name('create');
        Route::post('/', [PayrollController::class, 'store'])
            ->middleware('module.access:payroll,payroll_runs,write')
            ->name('store');
        Route::get('/complaints', [PayrollController::class, 'complaints'])
            ->middleware('module.access:payroll,payroll_runs,manage')
            ->name('complaints');
        Route::put('/complaints/{complaint}', [PayrollController::class, 'updateComplaint'])
            ->middleware('module.access:payroll,payroll_runs,manage')
            ->name('complaint-update');
        Route::get('/{payrollRun}', [PayrollController::class, 'show'])
            ->middleware('module.access:payroll,payroll_runs,read')
            ->name('show');
    });

    /*
    |----------------------------------------------------------------------
    | Documents Module Routes
    |----------------------------------------------------------------------
    */
    Route::prefix('documents')->name('documents.')->group(function () {
        // Employee self-service
        Route::get('/', [DocumentController::class, 'index'])->name('index');
        Route::get('/export-csv', [DocumentController::class, 'exportCsv'])->name('export-csv');
        Route::get('/download/{document}', [DocumentController::class, 'download'])->name('download');

        // Admin routes
        Route::get('/admin', [DocumentController::class, 'admin'])
            ->middleware('module.access:documents,documents,read')
            ->name('admin');
        Route::get('/create', [DocumentController::class, 'create'])
            ->middleware('module.access:documents,documents,write')
            ->name('create');
        Route::post('/', [DocumentController::class, 'store'])
            ->middleware('module.access:documents,documents,write')
            ->name('store');
        Route::get('/{document}', [DocumentController::class, 'show'])->name('show');
        Route::get('/{document}/edit', [DocumentController::class, 'edit'])
            ->middleware('module.access:documents,documents,write')
            ->name('edit');
        Route::put('/{document}', [DocumentController::class, 'update'])
            ->middleware('module.access:documents,documents,write')
            ->name('update');
        Route::delete('/{document}', [DocumentController::class, 'destroy'])
            ->middleware('module.access:documents,documents,manage')
            ->name('destroy');
    });

    /*
    |----------------------------------------------------------------------
    | Memos Module Routes
    |----------------------------------------------------------------------
    */
    Route::prefix('memos')->name('memos.')->group(function () {
        // Employee view (all authenticated users can browse published memos)
        Route::get('/', [MemoController::class, 'index'])->name('index');

        // Admin routes (must come before {memo} wildcard)
        Route::get('/admin', [MemoController::class, 'admin'])
            ->middleware('module.access:documents,memos,read')
            ->name('admin');
        Route::get('/create', [MemoController::class, 'create'])
            ->middleware('module.access:documents,memos,write')
            ->name('create');
        Route::post('/', [MemoController::class, 'store'])
            ->middleware('module.access:documents,memos,write')
            ->name('store');

        // Individual memo routes
        Route::get('/{memo}', [MemoController::class, 'show'])->name('show');
        Route::get('/{memo}/edit', [MemoController::class, 'edit'])
            ->middleware('module.access:documents,memos,write')
            ->name('edit');
        Route::put('/{memo}', [MemoController::class, 'update'])
            ->middleware('module.access:documents,memos,write')
            ->name('update');
        Route::delete('/{memo}', [MemoController::class, 'destroy'])
            ->middleware('module.access:documents,memos,manage')
            ->name('destroy');
    });

    /*
    |----------------------------------------------------------------------
    | Recruitment Module Routes
    |----------------------------------------------------------------------
    */
    Route::prefix('recruitment')->name('recruitment.')->middleware('module.access:hr_core,recruitment,read')->group(function () {
        Route::get('/', [RecruitmentController::class, 'index'])->name('index');
        Route::get('/export-csv', [RecruitmentController::class, 'exportCsv'])->name('export-csv');
        Route::get('/create', [RecruitmentController::class, 'create'])
            ->middleware('module.access:hr_core,recruitment,write')
            ->name('create');
        Route::post('/', [RecruitmentController::class, 'store'])
            ->middleware('module.access:hr_core,recruitment,write')
            ->name('store');
        Route::get('/{recruitment}', [RecruitmentController::class, 'show'])->name('show');
        Route::put('/{recruitment}', [RecruitmentController::class, 'update'])
            ->middleware('module.access:hr_core,recruitment,write')
            ->name('update');
        Route::put('/{recruitment}/status', [RecruitmentController::class, 'updateStatus'])
            ->middleware('module.access:hr_core,recruitment,write')
            ->name('update-status');
        Route::post('/{recruitment}/upload-file', [RecruitmentController::class, 'uploadFile'])
            ->middleware('module.access:hr_core,recruitment,write')
            ->name('upload-file');
        Route::post('/{recruitment}/transition', [RecruitmentController::class, 'transition'])
            ->middleware('module.access:hr_core,recruitment,manage')
            ->name('transition');
    });

    /*
    |----------------------------------------------------------------------
    | Account & Profile Routes
    |----------------------------------------------------------------------
    */
    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
        Route::get('/change-password', [AccountController::class, 'changePasswordForm'])->name('change-password');
        Route::post('/change-password', [AccountController::class, 'changePassword'])->name('change-password.update');
    });

    /*
    |----------------------------------------------------------------------
    | Compliance & Privacy Routes (Self-Service)
    |----------------------------------------------------------------------
    */

    // Data Correction Requests (employee self-service)
    Route::prefix('corrections')->name('corrections.')->group(function () {
        Route::get('/', [DataCorrectionController::class, 'index'])->name('index');
        Route::get('/create', [DataCorrectionController::class, 'create'])->name('create');
        Route::post('/', [DataCorrectionController::class, 'store'])->name('store');
    });

    // Privacy Consent (all authenticated users)
    Route::get('/privacy/consent', [PrivacyConsentController::class, 'show'])->name('privacy.consent');
    Route::post('/privacy/consent', [PrivacyConsentController::class, 'store'])->name('privacy.consent.store');
    Route::post('/privacy/consent/withdraw', [PrivacyConsentController::class, 'withdraw'])->name('privacy.consent.withdraw');

    /*
    |----------------------------------------------------------------------
    | Audit Trail Routes
    |----------------------------------------------------------------------
    */
    Route::get('/audit', [AuditController::class, 'index'])
        ->middleware('module.access:system,audit_logs,read')
        ->name('audit.index');

    /*
    |----------------------------------------------------------------------
    | Administration Routes
    |----------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->middleware('module.access:system,system_settings,read')->group(function () {
        Route::get('/', fn () => view('admin.index'))->name('index');

        // Branches
        Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
        Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
        Route::put('/branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
        Route::delete('/branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');

        // Payroll Configuration (unified: overtime rates + compensation templates)
        Route::get('/payroll-config', [PayrollConfigController::class, 'index'])->name('payroll-config.index');
        Route::put('/payroll-config/overtime-rates', [PayrollConfigController::class, 'updateOvertimeRates'])->name('payroll-config.update-overtime-rates');
        Route::post('/payroll-config/templates', [PayrollConfigController::class, 'storeTemplate'])->name('payroll-config.store-template');
        Route::put('/payroll-config/templates/{template}', [PayrollConfigController::class, 'updateTemplate'])->name('payroll-config.update-template');
        Route::delete('/payroll-config/templates/{template}', [PayrollConfigController::class, 'destroyTemplate'])->name('payroll-config.destroy-template');

        // Legacy redirects (keep old bookmarks working)
        Route::get('/compensation', fn () => redirect()->route('admin.payroll-config.index', ['tab' => 'allowances']))->name('compensation.index');
        Route::get('/overtime-rates', fn () => redirect()->route('admin.payroll-config.index', ['tab' => 'overtime-rates']))->name('overtime-rates.index');

        // Cutoff Periods
        Route::get('/cutoff-periods', [CutoffPeriodController::class, 'index'])->name('cutoff-periods.index');
        Route::post('/cutoff-periods', [CutoffPeriodController::class, 'store'])->name('cutoff-periods.store');
        Route::post('/cutoff-periods/populate', [CutoffPeriodController::class, 'populate'])->name('cutoff-periods.populate');
        Route::post('/cutoff-periods/{period}/close', [CutoffPeriodController::class, 'close'])->name('cutoff-periods.close');
        Route::post('/cutoff-periods/{period}/toggle-lock', [CutoffPeriodController::class, 'toggleLock'])->name('cutoff-periods.toggle-lock');
        Route::post('/cutoff-periods/{period}/cancel', [CutoffPeriodController::class, 'cancel'])->name('cutoff-periods.cancel');
        Route::delete('/cutoff-periods/{period}', [CutoffPeriodController::class, 'destroy'])->name('cutoff-periods.destroy');

        // Leave Settings
        Route::get('/leave-defaults', [LeaveSettingsController::class, 'defaults'])->name('leave-defaults');
        Route::post('/leave-defaults/globals', [LeaveSettingsController::class, 'saveGlobals'])->name('leave-defaults.save-globals');
        Route::post('/leave-defaults/department', [LeaveSettingsController::class, 'saveDepartment'])->name('leave-defaults.save-department');
        Route::get('/leave-entitlements', [LeaveSettingsController::class, 'entitlements'])->name('leave-entitlements');
        Route::put('/leave-entitlements/policy/{policy}', [LeaveSettingsController::class, 'updatePolicy'])->name('leave-entitlements.update-policy');

        // Work Schedules
        Route::get('/work-schedules', [WorkScheduleController::class, 'index'])->name('work-schedules.index');
        Route::post('/work-schedules/templates', [WorkScheduleController::class, 'storeTemplate'])->name('work-schedules.store-template');
        Route::put('/work-schedules/templates/{template}', [WorkScheduleController::class, 'updateTemplate'])->name('work-schedules.update-template');
        Route::delete('/work-schedules/templates/{template}', [WorkScheduleController::class, 'destroyTemplate'])->name('work-schedules.destroy-template');
        Route::post('/work-schedules/assign', [WorkScheduleController::class, 'assignTemplate'])->name('work-schedules.assign');

        // Approval Workflow
        Route::get('/approval-workflow', [ApprovalWorkflowController::class, 'index'])->name('approval-workflow.index');
        Route::post('/approval-workflow', [ApprovalWorkflowController::class, 'store'])->name('approval-workflow.store');
        Route::put('/approval-workflow/{approver}', [ApprovalWorkflowController::class, 'update'])->name('approval-workflow.update');
        Route::delete('/approval-workflow/{approver}', [ApprovalWorkflowController::class, 'destroy'])->name('approval-workflow.destroy');
        Route::post('/approval-workflow/reorder', [ApprovalWorkflowController::class, 'reorder'])->name('approval-workflow.reorder');

        // BIR Reports & Compliance
        Route::prefix('bir-reports')->name('bir-reports.')->group(function () {
            Route::get('/', [BirReportController::class, 'index'])->name('index');
            Route::get('/export-2316', [BirReportController::class, 'exportForm2316'])->name('export-2316');
            Route::get('/export-alphalist', [BirReportController::class, 'exportAlphalist'])->name('export-alphalist');
            Route::get('/export-remittance', [BirReportController::class, 'exportRemittance'])->name('export-remittance');
        });

        // Data Correction Requests (admin review)
        Route::get('/corrections', [DataCorrectionController::class, 'admin'])->name('corrections.index');
        Route::post('/corrections/{correction}/approve', [DataCorrectionController::class, 'approve'])->name('corrections.approve');
        Route::post('/corrections/{correction}/reject', [DataCorrectionController::class, 'reject'])->name('corrections.reject');

        // Privacy Consent Dashboard (admin)
        Route::get('/privacy-consents', [PrivacyConsentController::class, 'admin'])->name('privacy-consents.index');

        // System Monitoring
        Route::get('/system', [SystemController::class, 'index'])->name('system.index');

        // User Management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    /*
    |----------------------------------------------------------------------
    | Inventory & POS Module Routes
    |----------------------------------------------------------------------
    */
    Route::prefix('inventory')->name('inventory.')->middleware('module.access:inventory,inventory_items,read')->group(function () {
        // Items
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::get('/create', [InventoryController::class, 'create'])->name('create');
        Route::post('/', [InventoryController::class, 'store'])->name('store');
        Route::get('/items/{item}', [InventoryController::class, 'show'])->name('show');
        Route::get('/items/{item}/edit', [InventoryController::class, 'edit'])->name('edit');
        Route::put('/items/{item}', [InventoryController::class, 'update'])->name('update');
        Route::post('/items/{item}/adjust', [InventoryController::class, 'adjust'])->name('adjust');
        Route::post('/items/{item}/deactivate', [InventoryController::class, 'deactivate'])->name('deactivate');

        // Categories
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        // Suppliers
        Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers');
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

        // Locations
        Route::get('/locations', [LocationController::class, 'index'])->name('locations');
        Route::post('/locations', [LocationController::class, 'store'])->name('locations.store');
        Route::put('/locations/{location}', [LocationController::class, 'update'])->name('locations.update');
        Route::delete('/locations/{location}', [LocationController::class, 'destroy'])->name('locations.destroy');

        // POS
        Route::get('/pos', [PosController::class, 'index'])->name('pos');
        Route::get('/pos/items', [PosController::class, 'items'])->name('pos.items');
        Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');

        // Transactions
        Route::get('/transactions', [PosController::class, 'transactions'])->name('transactions');
        Route::post('/transactions/{transaction}/void', [PosController::class, 'voidTransaction'])->name('transactions.void');

        // Reports
        Route::get('/reports', [PosController::class, 'reports'])->name('reports');

        // Purchase Orders
        Route::get('/purchase-orders', [PosController::class, 'purchaseOrders'])->name('purchase-orders');
        Route::get('/purchase-orders/create', [PosController::class, 'createPurchaseOrder'])->name('purchase-orders.create');
        Route::post('/purchase-orders', [PosController::class, 'storePurchaseOrder'])->name('purchase-orders.store');
        Route::get('/purchase-orders/{po}', [PosController::class, 'showPurchaseOrder'])->name('purchase-orders.show');
        Route::post('/purchase-orders/{po}/status', [PosController::class, 'updatePurchaseOrderStatus'])->name('purchase-orders.update-status');
        Route::post('/purchase-orders/{po}/receive', [PosController::class, 'receivePurchaseOrder'])->name('purchase-orders.receive');
    });

    /*
    |----------------------------------------------------------------------
    | Notifications
    |----------------------------------------------------------------------
    */
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/feed', [NotificationController::class, 'feed'])->name('feed');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllRead'])->name('markAllRead');
        Route::post('/{notification}/mark-read', [NotificationController::class, 'markRead'])->name('markRead');
    });
});
