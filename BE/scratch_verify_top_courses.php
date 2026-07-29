<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$instructorId = 6;

echo "=== 1. CHECKING TOP 5 COURSES FOR INSTRUCTOR 6 IN DATABASE ===\n";

$topCourses = DB::table('courses as c')
    ->where('c.instructor_id', $instructorId)
    ->whereNull('c.deleted_at')
    ->select('c.id', 'c.title', 'c.status', 'c.thumbnail_url', 'c.level', 'c.price')
    ->get();

$courseIds = $topCourses->pluck('id')->toArray();

// Query enrollments count per course
$enrollments = DB::table('enrollments')
    ->whereIn('course_id', $courseIds)
    ->whereIn('status', ['active', 'completed'])
    ->select('course_id', DB::raw('COUNT(id) as enrollment_count'), DB::raw('COUNT(DISTINCT user_id) as unique_learner_count'))
    ->groupBy('course_id')
    ->get()
    ->keyBy('course_id');

// Query revenues per course (net instructor amount)
$revenues = DB::table('revenues')
    ->whereIn('course_id', $courseIds)
    ->whereIn('status', ['available', 'withdrawn'])
    ->select('course_id', DB::raw('SUM(instructor_amount) as total_revenue'))
    ->groupBy('course_id')
    ->get()
    ->keyBy('course_id');

$result = [];
foreach ($topCourses as $course) {
    $e = $enrollments->get($course->id);
    $r = $revenues->get($course->id);
    
    $eCount = $e ? (int)$e->enrollment_count : 0;
    $uCount = $e ? (int)$e->unique_learner_count : 0;
    $rev = $r ? (float)$r->total_revenue : 0.0;

    $result[] = [
        'id' => $course->id,
        'title' => $course->title,
        'status' => $course->status,
        'enrollment_count' => $eCount,
        'unique_learner_count' => $uCount,
        'revenue' => $rev,
        'price' => (float)$course->price
    ];
}

// Sort by enrollment_count desc
usort($result, fn($a, $b) => $b['enrollment_count'] <=> $a['enrollment_count']);

$top5 = array_slice($result, 0, 5);

echo json_encode($top5, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
