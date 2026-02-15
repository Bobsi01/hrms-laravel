<?php
/**
 * Quick module smoke test — hits every GET route as an authenticated admin user.
 * Run: php artisan tinker < tests/test_all_routes.php
 * Or:  php tests/test_all_routes.php
 */

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Auth;
use App\Models\User;

// Login as admin user
$user = User::where('email', 'bobis.daniel.bscs2023@gmail.com')->first();
if (!$user) {
    echo "ERROR: Admin user not found!\n";
    exit(1);
}
Auth::login($user);
echo "Logged in as: {$user->email} (ID: {$user->id})\n\n";

// Collect all GET routes (skip parameterized ones for now, we'll test those separately)
$routes = [
    // Dashboard
    '/' => 'Dashboard',
    
    // HR Core
    '/employees' => 'Employees Index',
    '/employees/create' => 'Employees Create',
    '/departments' => 'Departments Index',
    '/departments/create' => 'Departments Create',
    '/positions' => 'Positions Index',
    '/positions/create' => 'Positions Create',
    
    // Leave
    '/leave' => 'Leave Index',
    '/leave/create' => 'Leave Create',
    '/leave/admin' => 'Leave Admin',
    
    // Overtime
    '/overtime' => 'Overtime Index',
    '/overtime/create' => 'Overtime Create',
    '/overtime/admin' => 'Overtime Admin',
    
    // Attendance
    '/attendance/my' => 'My Attendance',
    '/attendance' => 'Attendance Admin',
    '/attendance/create' => 'Attendance Create',
    '/attendance/import' => 'Attendance Import',
    
    // Payroll
    '/payroll/my-payslips' => 'My Payslips',
    '/payroll' => 'Payroll Index',
    '/payroll/create' => 'Payroll Create',
    '/payroll/complaints' => 'Payroll Complaints',
    
    // Documents
    '/documents' => 'Documents Index',
    '/documents/admin' => 'Documents Admin',
    '/documents/create' => 'Documents Create',
    
    // Memos
    '/memos' => 'Memos Index',
    '/memos/admin' => 'Memos Admin',
    '/memos/create' => 'Memos Create',
    
    // Recruitment
    '/recruitment' => 'Recruitment Index',
    '/recruitment/create' => 'Recruitment Create',
    
    // Account
    '/account/profile' => 'Account Profile',
    '/account/change-password' => 'Change Password',
    
    // Compliance
    '/corrections' => 'Corrections Index',
    '/corrections/create' => 'Corrections Create',
    '/privacy/consent' => 'Privacy Consent',
    
    // Audit
    '/audit' => 'Audit Index',
    
    // Admin
    '/admin' => 'Admin Hub',
    '/admin/branches' => 'Branches',
    '/admin/payroll-config' => 'Payroll Config',
    '/admin/cutoff-periods' => 'Cutoff Periods',
    '/admin/leave-defaults' => 'Leave Defaults',
    '/admin/leave-entitlements' => 'Leave Entitlements',
    '/admin/work-schedules' => 'Work Schedules',
    '/admin/approval-workflow' => 'Approval Workflow',
    '/admin/bir-reports' => 'BIR Reports',
    '/admin/corrections' => 'Admin Corrections',
    '/admin/privacy-consents' => 'Privacy Consents Admin',
    '/admin/system' => 'System Monitor',
    '/admin/users' => 'User Management',
    '/admin/users/create' => 'Create User',
    
    // Inventory
    '/inventory' => 'Inventory Index',
    '/inventory/create' => 'Inventory Create',
    '/inventory/categories' => 'Categories',
    '/inventory/suppliers' => 'Suppliers',
    '/inventory/locations' => 'Locations',
    '/inventory/pos' => 'POS Terminal',
    '/inventory/transactions' => 'Transactions',
    '/inventory/reports' => 'Inventory Reports',
    '/inventory/purchase-orders' => 'Purchase Orders',
    '/inventory/purchase-orders/create' => 'Create Purchase Order',
    
    // Notifications
    '/notifications' => 'Notifications Index',
    '/notifications/feed' => 'Notifications Feed (JSON)',
];

$passed = 0;
$failed = 0;
$errors = [];

foreach ($routes as $uri => $label) {
    try {
        $request = \Illuminate\Http\Request::create($uri, 'GET');
        $request->setLaravelSession($app['session.store']);

        // Re-set auth for each request
        Auth::login($user);

        $response = $app->handle($request);
        $status = $response->getStatusCode();

        if ($status >= 200 && $status < 400) {
            echo "  PASS  [{$status}] {$label} ({$uri})\n";
            $passed++;
        } else {
            $body = $response->getContent();
            $errorSnippet = '';
            if ($status === 500 && preg_match('/<title>([^<]+)<\/title>/', $body, $m)) {
                $errorSnippet = " — " . trim($m[1]);
            }
            // Check if it's an exception message in the response
            if ($status === 500 && preg_match('/class="exception_message">([^<]+)</', $body, $m)) {
                $errorSnippet = " — " . trim($m[1]);
            }
            echo "  FAIL  [{$status}] {$label} ({$uri}){$errorSnippet}\n";
            $failed++;
            $errors[] = ['uri' => $uri, 'label' => $label, 'status' => $status, 'snippet' => $errorSnippet];
        }
    } catch (\Throwable $e) {
        $msg = get_class($e) . ': ' . $e->getMessage();
        // Truncate very long messages
        if (strlen($msg) > 200) $msg = substr($msg, 0, 200) . '...';
        echo "  FAIL  [EXC] {$label} ({$uri}) — {$msg}\n";
        $failed++;
        $errors[] = ['uri' => $uri, 'label' => $label, 'status' => 'EXC', 'snippet' => $msg];
    }
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "Results: {$passed} passed, {$failed} failed out of " . count($routes) . " routes\n";

if (!empty($errors)) {
    echo "\nFailed routes:\n";
    foreach ($errors as $e) {
        echo "  [{$e['status']}] {$e['label']} ({$e['uri']}){$e['snippet']}\n";
    }
}

echo "\n";
