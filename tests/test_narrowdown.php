<?php
/**
 * Narrow down: which extra operation causes the segfault?
 * Progressively add operations to find the trigger.
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$step = (int)($argv[1] ?? 0);
$uri = $argv[2] ?? '/positions';

echo "Step: $step, URL: $uri\n";

// Step 1: Auth::login
if ($step >= 1) {
    echo "  Auth::login...\n"; flush();
    $u = \App\Models\User::where('email', 'bobis.daniel.bscs2023@gmail.com')->first();
    \Illuminate\Support\Facades\Auth::login($u);
    echo "  Auth::login OK\n"; flush();
}

// Step 2: View::share permissions
if ($step >= 2) {
    echo "  View::share permissions...\n"; flush();
    $permService = app(\App\Services\PermissionService::class);
    $userPerms = $permService->getAllUserPermissions($u->id);
    \Illuminate\Support\Facades\View::share('userPermissions', $userPerms);
    \Illuminate\Support\Facades\View::share('currentUser', $u);
    \Illuminate\Support\Facades\View::share('isSystemAdmin', $u->isSystemAdmin());
    echo "  View::share OK\n"; flush();
}

// Step 3: Route match
echo "  Route match...\n"; flush();
$request = \Illuminate\Http\Request::create($uri, 'GET');
$route = app('router')->getRoutes()->match($request);
echo "  Matched: " . ($route->getName() ?? $route->uri()) . "\n"; flush();

// Step 4: gatherMiddleware
if ($step >= 3) {
    echo "  gatherMiddleware...\n"; flush();
    $mw = $route->gatherMiddleware();
    echo "  Middleware: " . implode(', ', $mw) . "\n"; flush();
}

echo "DONE\n";
