<?php
/**
 * Test: Does pre-compiling routes prevent the PCRE JIT crash?
 * Mode 1: match() WITHOUT pre-compilation (lazy)
 * Mode 2: match() WITH pre-compilation (eager)
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$testUrl = $argv[1] ?? '/positions';
$mode = $argv[2] ?? 'lazy';

$routes = app('router')->getRoutes();

if ($mode === 'eager') {
    echo "Mode: EAGER (pre-compiling all routes)\n";
    $count = 0;
    foreach ($routes->getRoutesByMethod()['GET'] ?? [] as $route) {
        $ref = new ReflectionMethod($route, 'compileRoute');
        $ref->setAccessible(true);
        $ref->invoke($route);
        $count++;
    }
    echo "Pre-compiled $count GET routes.\n";
} else {
    echo "Mode: LAZY (no pre-compilation)\n";
}

echo "Matching URL: $testUrl\n";
flush();

$request = \Illuminate\Http\Request::create($testUrl, 'GET');
try {
    $matched = $routes->match($request);
    echo "Result: " . ($matched->getName() ?? $matched->uri()) . "\n";
} catch (\Throwable $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
echo "DONE\n";
