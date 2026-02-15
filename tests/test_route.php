<?php
// Quick test for a single route
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// Bootstrap the framework
$bootstrapRequest = \Illuminate\Http\Request::capture();
$app->instance('request', $bootstrapRequest);

// Manually bootstrap
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::where('email', 'bobis.daniel.bscs2023@gmail.com')->first();
echo "User: {$user->email} (ID: {$user->id})\n";

\Illuminate\Support\Facades\Auth::login($user);

$uri = $argv[1] ?? '/positions';
echo "Testing: {$uri}\n";
flush();

// Register shutdown handler to catch segfaults
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        echo "\nSHUTDOWN ERROR: {$error['type']} - {$error['message']}\n";
        echo "File: {$error['file']}:{$error['line']}\n";
    }
});

try {
    echo "Creating request...\n"; flush();
    $request = \Illuminate\Http\Request::create($uri, 'GET');
    echo "Calling handle...\n"; flush();
    $response = $kernel->handle($request);
    echo "Handle done.\n"; flush();
    
    echo "Status: {$response->getStatusCode()}\n";
    echo "Length: " . strlen($response->getContent()) . "\n";
    
    if ($response->getStatusCode() >= 400) {
        $body = $response->getContent();
        if (preg_match('/SQLSTATE\[[^\]]+\][^:]*:\s*\d+\s+ERROR:\s*([^\n<]+)/s', $body, $m)) {
            echo "SQL Error: {$m[1]}\n";
        }
        if (preg_match('/exception_message[^>]*>([^<]+)/s', $body, $m)) {
            echo "Exception: " . trim($m[1]) . "\n";
        }
    }
} catch (\Throwable $e) {
    echo "EXCEPTION: " . get_class($e) . ": " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo substr($e->getTraceAsString(), 0, 1000) . "\n";
}
