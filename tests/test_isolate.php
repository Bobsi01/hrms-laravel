<?php
/**
 * Diagnostic: isolate whether the segfault is in middleware or controller+view.
 * Tests controller->method() directly WITHOUT going through $kernel->handle().
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$u = \App\Models\User::where('email', 'bobis.daniel.bscs2023@gmail.com')->first();
\Illuminate\Support\Facades\Auth::login($u);

// Share permissions to views (mimics SharePermissionsToView middleware)
$permService = app(\App\Services\PermissionService::class);
$userPerms = $permService->getAllUserPermissions($u->id);
\Illuminate\Support\Facades\View::share('userPermissions', $userPerms);
\Illuminate\Support\Facades\View::share('currentUser', $u);
\Illuminate\Support\Facades\View::share('isSystemAdmin', $u->isSystemAdmin());

$test = $argv[1] ?? 'controller';

if ($test === 'controller') {
    // Test: Call controller directly (no middleware pipeline)
    $uri = $argv[2] ?? '/positions';
    echo "Testing controller directly for: {$uri}\n"; flush();

    $request = \Illuminate\Http\Request::create($uri, 'GET');
    $request->setUserResolver(fn () => $u);
    app()->instance('request', $request);

    // Map URIs to controller calls
    $map = [
        '/positions' => [App\Http\Controllers\Positions\PositionController::class, 'index'],
        '/leave' => [App\Http\Controllers\Leave\LeaveController::class, 'index'],
        '/account/profile' => [App\Http\Controllers\Account\AccountController::class, 'profile'],
        '/audit' => [App\Http\Controllers\Audit\AuditController::class, 'index'],
        '/admin/branches' => [App\Http\Controllers\Admin\BranchController::class, 'index'],
        '/inventory' => [App\Http\Controllers\Inventory\InventoryController::class, 'index'],
        '/overtime' => [App\Http\Controllers\Overtime\OvertimeController::class, 'index'],
        '/documents' => [App\Http\Controllers\Documents\DocumentController::class, 'index'],
    ];

    if (!isset($map[$uri])) {
        echo "Unknown URI: {$uri}\n";
        exit(1);
    }

    [$class, $method] = $map[$uri];
    $controller = app($class);
    $response = $controller->$method($request);

    if ($response instanceof \Illuminate\View\View) {
        echo "Rendering view...\n"; flush();
        $html = $response->render();
        echo "Rendered OK, length=" . strlen($html) . "\n";
    } else {
        echo "Response type: " . get_class($response) . "\n";
        echo "Status: " . ($response->getStatusCode() ?? 'N/A') . "\n";
    }
    echo "DONE\n";

} elseif ($test === 'view-only') {
    // Test: Just render the view with minimal data
    $viewName = $argv[2] ?? 'positions.index';
    echo "Testing view render only: {$viewName}\n"; flush();

    $data = match ($viewName) {
        'positions.index' => ['positions' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20), 'canWrite' => false],
        'leave.index' => ['leaveRequests' => collect()],
        'account.profile' => ['user' => $u],
        'audit.index' => ['auditLogs' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50)],
        'admin.branches.index' => ['branches' => collect(), 'editBranch' => null, 'defaultBranchId' => 1],
        'inventory.index' => ['items' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 30), 'categories' => collect(), 'suppliers' => collect(), 'locations' => collect(), 'stats' => ['total'=>0,'low_stock'=>0,'out_of_stock'=>0,'in_stock'=>0]],
        'overtime.index' => ['overtimeRequests' => collect()],
        default => [],
    };

    $html = view($viewName, $data)->render();
    echo "Rendered OK, length=" . strlen($html) . "\n";
    echo "DONE\n";

} elseif ($test === 'route-match') {
    // Test: Just route matching (no controller execution)
    $uri = $argv[2] ?? '/positions';
    echo "Testing route match only: {$uri}\n"; flush();

    $request = \Illuminate\Http\Request::create($uri, 'GET');
    $route = app('router')->getRoutes()->match($request);
    echo "Matched route: " . ($route->getName() ?? $route->uri()) . "\n";
    echo "Middleware: " . implode(', ', $route->gatherMiddleware()) . "\n";
    echo "DONE\n";
}
