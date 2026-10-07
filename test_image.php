<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$testPaths = [
    'attendance/1/test.png',
    'storage/attendance/1/test.png',
    'http://example.com/storage/attendance/1/test.png',
    'data:image/png;base64,abc123',
];

foreach ($testPaths as $path) {
    $result = \App\Models\EmployeeAttendance::formatImageUrl($path);
    echo "Input: {$path}\n";
    echo "Output: " . ($result ?? 'null') . "\n\n";
}