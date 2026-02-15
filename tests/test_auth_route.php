<?php
// Test route with full authentication
// Run: php -d opcache.enable=0 tests/test_auth_route.php /positions

require __DIR__ . '/../vendor/autoload.php';

$route = $argv[1] ?? '/positions';
echo "Testing route: {$route}\n";

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// Bootstrap the application by handling a dummy request first
$bootRequest = \Illuminate\Http\Request::create('/login', 'GET');
$kernel->handle($bootRequest);

// Now find and auth the user
$user = \App\Models\User::find(0);
if (!$user) {
    echo "User not found!\n";
    exit(1);
}

echo "Logged in as: {$user->email}\n";

// Create request with auth
$request = \Illuminate\Http\Request::create($route, 'GET');
$session = new \Illuminate\Session\Store('test', new \Illuminate\Session\ArraySessionHandler(120));
$session->put('_token', 'test');
$request->setLaravelSession($session);
$request->setUserResolver(fn() => $user);

// Set auth guard
\Illuminate\Support\Facades\Auth::setUser($user);

echo "Dispatching...\n";
$response = $kernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content length: " . strlen($response->getContent()) . "\n";
