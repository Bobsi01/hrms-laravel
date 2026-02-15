<?php
/**
 * Test: Does PositionController crash with JUST the autoloader, no app bootstrap?
 */

// Step 0: Just autoloader
$step = (int)($argv[1] ?? 0);
echo "Step: $step\n"; flush();

if ($step >= 0) {
    echo "  Loading autoloader...\n"; flush();
    require __DIR__ . '/../vendor/autoload.php';
    echo "  Autoloader OK\n"; flush();
}

if ($step >= 1) {
    echo "  Creating app...\n"; flush();
    $app = require __DIR__ . '/../bootstrap/app.php';
    echo "  App OK\n"; flush();
}

if ($step >= 2) {
    echo "  Bootstrapping kernel...\n"; flush();
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    echo "  Bootstrap OK\n"; flush();
}

echo "  class_exists(PositionController)...\n"; flush();
$result = class_exists('App\Http\Controllers\Positions\PositionController');
echo "  Result: " . ($result ? 'YES' : 'NO') . "\n"; flush();
echo "DONE\n";
