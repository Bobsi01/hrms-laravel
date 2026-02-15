<?php
/**
 * Test: find the exact point of PCRE JIT crash during route matching.
 * Collects all unique route regexes, then tests preg_match with JIT enabled.
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$routes = app('router')->getRoutes();

// Force compilation by matching a known-safe URL
$safeReq = \Illuminate\Http\Request::create('/up', 'GET');
try { $routes->match($safeReq); } catch (\Throwable $e) {}

// Force compilation of ALL routes using reflection
$regexes = [];
foreach ($routes->getRoutesByMethod()['GET'] ?? [] as $route) {
    $ref = new ReflectionMethod($route, 'compileRoute');
    $ref->setAccessible(true);
    $ref->invoke($route);
    
    $compiled = $route->getCompiled();
    if ($compiled) {
        $regex = $compiled->getRegex();
        if (!isset($regexes[$regex])) {
            $regexes[$regex] = $route->getName() ?? $route->uri();
        }
    }
}

echo "Total unique GET route regexes: " . count($regexes) . "\n";

$testUrl = $argv[1] ?? '/positions';
echo "Testing URL: $testUrl\n";
echo "Testing " . count($regexes) . " unique patterns...\n\n";

// Test each regex one by one
$count = 0;
foreach ($regexes as $regex => $routeName) {
    $count++;
    $result = preg_match($regex, rawurldecode($testUrl));
    $tag = $result ? "MATCHED" : "no";
    echo "  [$count] $routeName (len=" . strlen($regex) . ") => $tag\n";
    flush();
    
    if ($result) {
        echo "\n  → Match found at iteration #$count\n";
        break;
    }
}

echo "\nCompleted $count regex tests without crash.\n";

// Now test the ACTUAL route matching via matchAgainstRoutes style loop
echo "\n--- Phase 2: Simulating matchAgainstRoutes() ---\n";
echo "Testing routes->match() for $testUrl...\n";
flush();

$request = \Illuminate\Http\Request::create($testUrl, 'GET');
try {
    $matched = $routes->match($request);
    echo "Match result: " . ($matched->getName() ?? $matched->uri()) . "\n";
} catch (\Throwable $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
echo "DONE\n";
