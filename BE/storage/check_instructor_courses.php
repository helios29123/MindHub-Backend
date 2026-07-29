<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$instructors = \App\Models\User::where('role', 'instructor')->get();
foreach ($instructors as $u) {
    $count = \App\Models\Course::where('instructor_id', $u->id)->count();
    $statuses = \App\Models\Course::where('instructor_id', $u->id)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status')->toArray();
    echo "ID: {$u->id} | Name: {$u->name} | Email: {$u->email} | Total Courses: {$count} | Statuses: " . json_encode($statuses) . PHP_EOL;
}
