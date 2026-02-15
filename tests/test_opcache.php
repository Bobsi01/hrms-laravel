<?php
// Test route with OPcache disabled
// Run: php -d opcache.enable=0 -d opcache.enable_cli=0 tests/test_opcache.php /positions

require __DIR__ . '/../vendor/autoload.php';

$route = $argv[1] ?? '/positions';
echo "Testing route: {$route}\n";
echo "OPcache enabled: " . (function_exists('opcache_get_status') ? (opcache_get_status(false)['opcache_enabled'] ?? 'unknown') : 'extension not loaded') . "\n";

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

$request = \Illuminate\Http\Request::create($route, 'GET');
$request->setLaravelSession(new \Illuminate\Session\Store('test', new \Illuminate\Session\ArraySessionHandler(120)));
$request->setUserResolver(fn() => \App\Models\User::find(0));

echo "Dispatching...\n";
$response = $kernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content length: " . strlen($response->getContent()) . "\n";
