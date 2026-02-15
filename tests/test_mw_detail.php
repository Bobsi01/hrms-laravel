<?php
/**
 * Narrow down: Is it gatherMiddleware's controller instantiation that crashes?
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$u = \App\Models\User::where('email', 'bobis.daniel.bscs2023@gmail.com')->first();
\Illuminate\Support\Facades\Auth::login($u);

$uri = $argv[1] ?? '/positions';
$step = $argv[2] ?? 'all';

echo "URL: $uri, Step: $step\n";

$request = \Illuminate\Http\Request::create($uri, 'GET');
$route = app('router')->getRoutes()->match($request);
echo "Matched: " . ($route->getName() ?? $route->uri()) . "\n"; flush();

if ($step === 'route-mw') {
    // Just the route-level middleware (no controller middleware)
    echo "Route middleware (no controller)...\n"; flush();
    $mw = (array) ($route->action['middleware'] ?? []);
    echo "Middleware: " . implode(', ', $mw) . "\n"; flush();
}

if ($step === 'is-controller') {
    echo "isControllerAction...\n"; flush();
    $ref = new ReflectionMethod($route, 'isControllerAction');
    $ref->setAccessible(true);
    $result = $ref->invoke($route);
    echo "isControllerAction: " . ($result ? 'true' : 'false') . "\n"; flush();
}

if ($step === 'get-class') {
    echo "getControllerClass...\n"; flush();
    $class = $route->getControllerClass();
    echo "Class: $class\n"; flush();
}

if ($step === 'get-method') {
    echo "getControllerMethod...\n"; flush();
    $ref = new ReflectionMethod($route, 'getControllerMethod');
    $ref->setAccessible(true);
    $method = $ref->invoke($route);
    echo "Method: $method\n"; flush();
}

if ($step === 'instantiate') {
    echo "Instantiating controller...\n"; flush();
    $controller = $route->getController();
    echo "Controller: " . get_class($controller) . "\n"; flush();
}

if ($step === 'has-mw') {
    echo "Checking HasMiddleware...\n"; flush();
    $class = $route->getControllerClass();
    $has = is_a($class, \Illuminate\Routing\Controllers\HasMiddleware::class, true);
    echo "HasMiddleware: " . ($has ? 'true' : 'false') . "\n"; flush();
    
    $hasGetMw = method_exists($class, 'getMiddleware');
    echo "has getMiddleware: " . ($hasGetMw ? 'true' : 'false') . "\n"; flush();
}

if ($step === 'all') {
    echo "Full gatherMiddleware...\n"; flush();
    $mw = $route->gatherMiddleware();
    echo "Middleware: " . implode(', ', $mw) . "\n"; flush();
}

echo "DONE\n";
