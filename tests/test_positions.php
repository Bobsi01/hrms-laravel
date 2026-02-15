<?php
// Test positions query without view rendering
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$u = \App\Models\User::where('email', 'bobis.daniel.bscs2023@gmail.com')->first();
\Illuminate\Support\Facades\Auth::login($u);
echo "User: {$u->email}\n";

echo "Testing positions query...\n"; flush();
$p = \App\Models\Position::with('department')->withCount('employees')->orderBy('name')->paginate(20);
echo "Found: {$p->total()} positions\n"; flush();
echo "First: " . ($p->first()->name ?? 'none') . "\n"; flush();

echo "\nNow testing Blade render...\n"; flush();
$html = view('positions.index', [
    'positions' => $p,
    'canWrite' => true,
])->render();
echo "View length: " . strlen($html) . "\n";
echo "OK\n";
