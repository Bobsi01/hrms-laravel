<?php
// Test multiple routes one at a time via kernel->handle
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$u = \App\Models\User::where('email', 'bobis.daniel.bscs2023@gmail.com')->first();
\Illuminate\Support\Facades\Auth::login($u);

$routes = [
    '/positions',
    '/leave',
    '/overtime',
    '/attendance/my',
    '/attendance',
    '/payroll/my-payslips',
    '/payroll',
    '/documents',
    '/memos',
    '/recruitment',
    '/account/profile',
    '/corrections',
    '/audit',
    '/admin',
    '/admin/branches',
    '/notifications',
    '/inventory',
];

// Only test ONE route (passed as arg) to isolate segfault
$uri = $argv[1] ?? '/positions';
echo "Testing: {$uri}\n"; flush();

$request = \Illuminate\Http\Request::create($uri, 'GET');
$response = $kernel->handle($request);

echo "Status: {$response->getStatusCode()}\n";
if ($response->getStatusCode() >= 400) {
    $body = $response->getContent();
    if (preg_match('/SQLSTATE[^:]*:\s*\d+\s+ERROR:\s*([^\n<]+)/s', $body, $m)) {
        echo "SQL: {$m[1]}\n";
    }
}
echo "OK\n";
