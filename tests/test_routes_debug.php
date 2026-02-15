<?php
/**
 * Dump the compiled route patterns to understand the regex structure.
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$routes = app('router')->getRoutes();

// Get compiled routes info
echo "=== Route Collection Info ===\n";
echo "Total routes: " . count($routes) . "\n\n";

// Check what type of route collection
echo "Collection class: " . get_class($routes) . "\n\n";

// Look at the compiled route data
$compiled = $routes->getRoutes();
echo "=== Routes with regex patterns ===\n";
foreach ($compiled as $route) {
    $uri = $route->uri();
    $compiled_route = $route->getCompiled();
    if ($compiled_route) {
        $regex = $compiled_route->getRegex();
        // Only show routes with significant regex (skip simple ones)
        echo sprintf("  %-35s  %s\n", $route->getName() ?? $uri, substr($regex, 0, 100));
    }
}

echo "\n=== Testing preg_match directly ===\n";
// Find the match method internals
// Symfony uses preg_match against each route's compiled regex
$testUrls = [
    '/positions' => 'CRASHES',
    '/leave' => 'CRASHES',
    '/overtime' => 'WORKS',
    '/admin' => 'WORKS',
    '/admin/branches' => 'CRASHES',
    '/audit' => 'CRASHES',
    '/inventory' => 'CRASHES',
    '/documents' => 'WORKS',
    '/account/profile' => 'CRASHES',
    '/memos' => 'WORKS',
];

// Try matching each route manually
foreach ($compiled as $route) {
    $compiledRoute = $route->getCompiled();
    if (!$compiledRoute) continue;
    
    $regex = $compiledRoute->getRegex();
    
    foreach ($testUrls as $url => $status) {
        if (preg_match($regex, $url)) {
            echo "  $url => matched by route '{$route->uri()}' (regex len=" . strlen($regex) . ") [$status]\n";
        }
    }
}
