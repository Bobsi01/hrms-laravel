<?php
/**
 * Smoke-test all modules by building requests through the Laravel kernel.
 * Usage: php tests/smoke_test.php
 * 
 * This tests each route one at a time with proper error handling.
 */

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// Boot the app
$app->boot();

// Login the user through the auth system
$user = \App\Models\User::where('email', 'bobis.daniel.bscs2023@gmail.com')->first();
if (!$user) {
    echo "ERROR: Admin user not found!\n";
    exit(1);
}

echo "User: {$user->email} (ID: {$user->id})\n\n";

$routes = [
    '/' => 'Dashboard',
    '/employees' => 'Employees Index',
    '/employees/create' => 'Employees Create',
    '/departments' => 'Departments Index',
    '/departments/create' => 'Departments Create',
    '/positions' => 'Positions Index',
    '/positions/create' => 'Positions Create',
    '/leave' => 'Leave Index',
    '/leave/create' => 'Leave Create',
    '/leave/admin' => 'Leave Admin',
    '/overtime' => 'Overtime Index',
    '/overtime/create' => 'Overtime Create',
    '/overtime/admin' => 'Overtime Admin',
    '/attendance/my' => 'My Attendance',
    '/attendance' => 'Attendance Admin',
    '/attendance/create' => 'Attendance Create',
    '/attendance/import' => 'Attendance Import',
    '/payroll/my-payslips' => 'My Payslips',
    '/payroll' => 'Payroll Index',
    '/payroll/create' => 'Payroll Create',
    '/payroll/complaints' => 'Payroll Complaints',
    '/documents' => 'Documents Index',
    '/documents/admin' => 'Documents Admin',
    '/documents/create' => 'Documents Create',
    '/memos' => 'Memos Index',
    '/memos/admin' => 'Memos Admin',
    '/memos/create' => 'Memos Create',
    '/recruitment' => 'Recruitment Index',
    '/recruitment/create' => 'Recruitment Create',
    '/account/profile' => 'Account Profile',
    '/account/change-password' => 'Change Password',
    '/corrections' => 'Corrections Index',
    '/corrections/create' => 'Corrections Create',
    '/privacy/consent' => 'Privacy Consent',
    '/audit' => 'Audit Trail',
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
    '/inventory' => 'Inventory Index',
    '/inventory/create' => 'Inventory Create',
    '/inventory/categories' => 'Categories',
    '/inventory/suppliers' => 'Suppliers',
    '/inventory/locations' => 'Locations',
    '/inventory/pos' => 'POS Terminal',
    '/inventory/transactions' => 'Transactions',
    '/inventory/reports' => 'Inventory Reports',
    '/inventory/purchase-orders' => 'Purchase Orders',
    '/inventory/purchase-orders/create' => 'Create PO',
    '/notifications' => 'Notifications Index',
];

$passed = 0;
$failed = 0;
$errors = [];

foreach ($routes as $uri => $label) {
    try {
        // Create a fresh request
        $request = \Illuminate\Http\Request::create($uri, 'GET');
        
        // Set up session
        $session = new \Illuminate\Session\Store(
            'test_session',
            new \Illuminate\Session\CookieSessionHandler(
                $app['cookie'],
                config('session.lifetime', 120),
                false
            ),
            \Illuminate\Support\Str::random(40)
        );
        $session->start();
        $request->setLaravelSession($session);
        
        // Auth the user
        \Illuminate\Support\Facades\Auth::login($user);
        
        // Handle the request
        $response = $kernel->handle($request);
        $status = $response->getStatusCode();
        
        if ($status >= 200 && $status < 400) {
            echo "  PASS  [{$status}] {$label} ({$uri})\n";
            $passed++;
        } else {
            // Try to extract error from response
            $body = $response->getContent();
            $errorMsg = '';
            // Check for exception_message in Whoops/debug page
            if (preg_match('/exception_message[^>]*>([^<]+)/s', $body, $m)) {
                $errorMsg = ' — ' . trim($m[1]);
            } elseif (preg_match('/<h2[^>]*class="exception-message[^>]*>([^<]+)/s', $body, $m)) {
                $errorMsg = ' — ' . trim($m[1]);
            } elseif (preg_match('/SQLSTATE\[([^\]]+)\][^:]*:\s*\d+\s+ERROR:\s*([^\n]+)/s', $body, $m)) {
                $errorMsg = " — SQLSTATE[{$m[1]}]: {$m[2]}";
            }
            echo "  FAIL  [{$status}] {$label} ({$uri}){$errorMsg}\n";
            $failed++;
            $errors[] = compact('uri', 'label', 'status', 'errorMsg');
        }
        
        // Terminate the request (cleanup)
        $kernel->terminate($request, $response);
        
    } catch (\Throwable $e) {
        $msg = get_class($e) . ': ' . substr($e->getMessage(), 0, 200);
        echo "  FAIL  [EXC] {$label} ({$uri}) — {$msg}\n";
        $failed++;
        $errors[] = ['uri' => $uri, 'label' => $label, 'status' => 'EXC', 'errorMsg' => $msg];
    }
    
    // Flush — ensure output is visible
    flush();
}

echo "\n" . str_repeat('=', 65) . "\n";
echo "Results: {$passed} passed, {$failed} failed out of " . count($routes) . " routes\n";

if (!empty($errors)) {
    echo "\nFailed routes:\n";
    foreach ($errors as $e) {
        echo "  [{$e['status']}] {$e['label']} ({$e['uri']}){$e['errorMsg']}\n";
    }
}
echo "\n";
