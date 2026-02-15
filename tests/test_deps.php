<?php
/**
 * Test: Which imported class causes PositionController to crash?
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$u = \App\Models\User::where('email', 'bobis.daniel.bscs2023@gmail.com')->first();
\Illuminate\Support\Facades\Auth::login($u);

$step = (int)($argv[1] ?? 0);

echo "Step: $step\n"; flush();

// Pre-load dependencies one by one, then try loading the controller
$deps = [
    'App\Http\Controllers\Controller',
    'App\Models\Department',
    'App\Models\Position',
    'App\Models\PositionAccessPermission',
    'App\Services\AuditService',
    'App\Services\PermissionCatalog',
    'App\Services\PermissionService',
    'Illuminate\Http\Request',
];

for ($i = 0; $i < min($step, count($deps)); $i++) {
    echo "  Pre-loading: {$deps[$i]}... "; flush();
    class_exists($deps[$i]);
    echo "OK\n"; flush();
}

echo "  Now loading PositionController...\n"; flush();
$result = class_exists('App\Http\Controllers\Positions\PositionController');
echo "  Result: " . ($result ? 'YES' : 'NO') . "\n"; flush();

echo "DONE\n";
