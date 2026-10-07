<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$admin = \App\Models\User::where('name', 'Admin')->first();
$count = \App\Models\EmployeeAttendance::where('employee_id', $admin?->id)->count();
echo "Attendance records for Admin: " . $count . PHP_EOL;

$allEmployeesInAttendance = \App\Models\EmployeeAttendance::with('employee')->get()->pluck('employee.name')->unique();
echo "Unique employees in attendance table: " . json_encode($allEmployeesInAttendance->toArray()) . PHP_EOL;





