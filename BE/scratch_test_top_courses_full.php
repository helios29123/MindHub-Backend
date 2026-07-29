<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$user = User::where('email', 'instructor1@mindhub.test')->first();
Auth::login($user);

echo "=== 1. VERIFYING TOP 5 COURSES FOR INSTRUCTOR 6 ===\n";

$instructorId = $user->id;

// Step A: Get all published/active courses belonging to instructor 6
$courses = DB::table('courses')
    ->where('instructor_id', $instructorId)
    ->whereNull('deleted_at')
    ->get(['id', 'title', 'status', 'thumbnail_url', 'level', 'price']);

$courseIds = $courses->pluck('id')->toArray();

// Step B: Aggregate enrollments per course
$enrollmentsMap = DB::table('enrollments')
    ->whereIn('course_id', $courseIds)
    ->whereIn('status', ['active', 'completed'])
    ->select('course_id', DB::raw('COUNT(id) as enrollment_count'), DB::raw('COUNT(DISTINCT user_id) as unique_learner_count'))
    ->groupBy('course_id')
    ->get()
    ->keyBy('course_id');

// Step C: Aggregate revenues per course
$revenuesMap = DB::table('revenues')
    ->whereIn('course_id', $courseIds)
    ->whereIn('status', ['available', 'withdrawn'])
    ->select('course_id', DB::raw('COALESCE(SUM(instructor_amount), 0) as total_instructor_revenue'), DB::raw('COALESCE(SUM(gross_amount), 0) as total_gross_revenue'))
    ->groupBy('course_id')
    ->get()
    ->keyBy('course_id');

$items = [];
$rank = 1;
foreach ($courses as $c) {
    $e = $enrollmentsMap->get($c->id);
    $r = $revenuesMap->get($c->id);

    $eCount = $e ? (int) $e->enrollment_count : 0;
    $uCount = $e ? (int) $e->unique_learner_count : 0;
    $instRev = $r ? (float) $r->total_instructor_revenue : 0.0;
    $grossRev = $r ? (float) $r->total_gross_revenue : 0.0;

    $items[] = [
        'id' => (int) $c->id,
        'course_id' => (int) $c->id,
        'title' => $c->title,
        'status' => $c->status,
        'thumbnail_url' => $c->thumbnail_url,
        'level' => $c->level ?? 'beginner',
        'enrollment_count' => $eCount,
        'enrollments_count' => $eCount,
        'student_count' => $uCount,
        'learners_count' => $uCount,
        'unique_learner_count' => $uCount,
        'revenue' => $instRev,
        'instructor_revenue' => $instRev,
        'gross_revenue' => $grossRev,
        'price' => (float) ($c->price ?? 0),
    ];
}

usort($items, function ($a, $b) {
    if ($b['enrollment_count'] !== $a['enrollment_count']) {
        return $b['enrollment_count'] <=> $a['enrollment_count'];
    }
    return $b['revenue'] <=> $a['revenue'];
});

$top5 = array_slice($items, 0, 5);
foreach ($top5 as $idx => &$item) {
    $item['rank'] = $idx + 1;
}

echo json_encode($top5, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
