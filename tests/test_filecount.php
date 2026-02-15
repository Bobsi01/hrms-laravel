<?php
/**
 * Count loaded files at crash time
 */
require __DIR__ . '/../vendor/autoload.php';

echo "Files loaded after autoloader: " . count(get_included_files()) . "\n"; flush();

// Try loading PositionController
echo "Loading PositionController...\n"; flush();
$before = count(get_included_files());
$result = class_exists('App\Http\Controllers\Positions\PositionController');
echo "Files loaded: $before -> " . count(get_included_files()) . "\n"; flush();
echo "DONE\n";
