<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== INSTRUCTOR PROFILES ===\n";
print_r(DB::table('instructor_profiles')->get()->toArray());

echo "=== COURSES INSTRUCTOR_ID BREAKDOWN ===\n";
print_r(DB::select("SELECT instructor_id, status, COUNT(*) as count FROM courses GROUP BY instructor_id, status"));

echo "=== COURSES FOR USER 6 / INSTRUCTOR PROFILES ===\n";
print_r(DB::select("SELECT id, title, status, instructor_id FROM courses WHERE instructor_id IN (1, 6) LIMIT 40"));

echo "=== ENROLLMENTS BY COURSE ===\n";
print_r(DB::select("SELECT course_id, COUNT(*) as count FROM enrollments GROUP BY course_id LIMIT 30"));
