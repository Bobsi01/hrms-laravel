<?php
/**
 * Deep-dive into route matching to find the exact preg_match that crashes.
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Trace preg_match calls by examining the route matching process
$routes = app('router')->getRoutes();

// Force route compilation by matching a known-safe URL first
echo "Compiling routes with safe match...\n";
$safeReq = \Illuminate\Http\Request::create('/', 'GET');
try { $routes->match($safeReq); } catch (\Exception $e) {}

echo "Total routes: " . count($routes->getRoutes()) . "\n\n";

// Check compiled route info
echo "=== Route compilation info ===\n";
$allRoutes = $routes->getRoutes();
$withRegex = 0;
$withoutRegex = 0;

foreach ($allRoutes as $route) {
    $route->bind($safeReq); // force compile
    $c = $route->getCompiled();
    if ($c) {
        $withRegex++;
    } else {
        $withoutRegex++;
    }
}
echo "Routes with compiled regex: $withRegex\n";
echo "Routes without: $withoutRegex\n\n";

// Now show the regex patterns for routes that match our test URLs
echo "=== Compiled regexes for relevant routes ===\n";
$testPrefixes = ['positions', 'leave', 'overtime', 'admin', 'audit', 'inventory', 'documents', 'account', 'memos'];

foreach ($allRoutes as $route) {
    $uri = $route->uri();
    $name = $route->getName() ?? '';
    
    $relevant = false;
    foreach ($testPrefixes as $prefix) {
        if (str_starts_with($uri, $prefix) || str_starts_with($uri, '/' . $prefix)) {
            $relevant = true;
            break;
        }
    }
    if (!$relevant) continue;
    
    $c = $route->getCompiled();
    if ($c) {
        $regex = $c->getRegex();
        $staticPrefix = $c->getStaticPrefix();
        echo sprintf("  %-40s prefix=%-20s regex_len=%d\n", 
            ($name ?: $uri), $staticPrefix, strlen($regex));
    }
}

echo "\n=== Testing individual route regex matching ===\n";
$crashUrls = ['/positions', '/leave', '/audit', '/inventory', '/account/profile', '/admin/branches'];
$workUrls = ['/overtime', '/documents', '/memos', '/admin'];

foreach ($allRoutes as $route) {
    $c = $route->getCompiled();
    if (!$c) continue;
    $regex = $c->getRegex();
    
    foreach (array_merge($crashUrls, $workUrls) as $url) {
        $matches = [];
        $result = @preg_match($regex, $url, $matches);
        if ($result === 1) {
            $tag = in_array($url, $crashUrls) ? 'CRASH-ROUTE' : 'WORK-ROUTE';
            echo "  [$tag] $url => matched by '{$route->uri()}' (name={$route->getName()}, regex_len=" . strlen($regex) . ")\n";
        }
    }
}

echo "\n=== Checking CompiledRouteCollection ===\n";
// In Laravel 12, look for the compiled matcher
$ref = new ReflectionClass($routes);
foreach ($ref->getMethods() as $method) {
    if (str_contains(strtolower($method->getName()), 'compil') || str_contains(strtolower($method->getName()), 'match')) {
        echo "  Method: {$method->getName()}\n";
    }
}
