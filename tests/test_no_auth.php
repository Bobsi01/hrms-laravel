<?php
/**
 * Test: Does PositionController crash without Auth::login?
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Loading PositionController (no auth)...\n"; flush();
$result = class_exists('App\Http\Controllers\Positions\PositionController');
echo "Result: " . ($result ? 'YES' : 'NO') . "\n"; flush();
echo "DONE\n";
