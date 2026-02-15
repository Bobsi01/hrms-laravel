<?php
/**
 * Narrow: Is it is_a() or the class resolution?
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$u = \App\Models\User::where('email', 'bobis.daniel.bscs2023@gmail.com')->first();
\Illuminate\Support\Facades\Auth::login($u);

$step = (int)($argv[1] ?? 0);

echo "Step: $step\n"; flush();

// Step 0: reference the interface
if ($step >= 0) {
    echo "  Loading HasMiddleware interface...\n"; flush();
    $iface = \Illuminate\Routing\Controllers\HasMiddleware::class;
    echo "  Interface: $iface\n"; flush();
}

// Step 1: reference the controller class
if ($step >= 1) {
    echo "  Loading PositionController class...\n"; flush();
    $class = \App\Http\Controllers\Positions\PositionController::class;
    echo "  Class: $class\n"; flush();
}

// Step 2: class_exists check
if ($step >= 2) {
    echo "  class_exists(PositionController)...\n"; flush();
    $exists = class_exists($class);
    echo "  Exists: " . ($exists ? 'true' : 'false') . "\n"; flush();
}

// Step 3: is_a check
if ($step >= 3) {
    echo "  is_a(PositionController, HasMiddleware, true)...\n"; flush();
    $result = is_a($class, $iface, true);
    echo "  is_a: " . ($result ? 'true' : 'false') . "\n"; flush();
}

// Step 4: Route match first, then do the check
if ($step >= 4) {
    echo "  Route matching /positions...\n"; flush();
    $request = \Illuminate\Http\Request::create('/positions', 'GET');
    $route = app('router')->getRoutes()->match($request);
    echo "  Matched: " . ($route->getName()) . "\n"; flush();
    
    echo "  getControllerClass from route...\n"; flush();
    $routeClass = $route->getControllerClass();
    echo "  Route class: $routeClass\n"; flush();
    
    echo "  is_a from route class...\n"; flush();
    $result2 = is_a($routeClass, $iface, true);
    echo "  is_a: " . ($result2 ? 'true' : 'false') . "\n"; flush();
}

echo "DONE\n";
