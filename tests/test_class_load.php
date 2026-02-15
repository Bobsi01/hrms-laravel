<?php
/**
 * Test: class_exists for crashing vs working controllers
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$u = \App\Models\User::where('email', 'bobis.daniel.bscs2023@gmail.com')->first();
\Illuminate\Support\Facades\Auth::login($u);

$controllers = [
    // Working group first
    'App\Http\Controllers\Overtime\OvertimeController',
    'App\Http\Controllers\Documents\DocumentController',
    'App\Http\Controllers\Memos\MemoController',
    'App\Http\Controllers\Payroll\PayrollController',
    'App\Http\Controllers\Attendance\AttendanceController',
    // Crashing group
    'App\Http\Controllers\Positions\PositionController',
    'App\Http\Controllers\Leave\LeaveController',
    'App\Http\Controllers\Account\AccountController',
    'App\Http\Controllers\Audit\AuditController',
    'App\Http\Controllers\Admin\BranchController',
    'App\Http\Controllers\Inventory\InventoryController',
];

$target = $argv[1] ?? 'all';

if ($target === 'all') {
    foreach ($controllers as $class) {
        echo "class_exists($class)... "; flush();
        $e = class_exists($class);
        echo ($e ? 'YES' : 'NO') . "\n"; flush();
    }
} else {
    echo "class_exists($target)... "; flush();
    $e = class_exists($target);
    echo ($e ? 'YES' : 'NO') . "\n"; flush();
}

echo "DONE\n";
