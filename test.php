<?php
require 'vendor/autoload.php';
$tasks = \App\Models\EmployeeTask::take(3)->get();
foreach ($tasks as $t) {
    $s = $t->start_date ? : "NULL";
    $e = $t->end_date ? : "NULL";
    echo "ID: " . $t->id . " Title: " . $t->title . " start: " . $s . " end: " . $e . PHP_EOL;
}