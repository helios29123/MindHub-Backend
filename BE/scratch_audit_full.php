<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== USER AUDIT ===\n";
$user = DB::table('users')->where('email', 'instructor1@mindhub.test')->first();
print_r($user);

echo "\n=== INSTRUCTOR PROFILE AUDIT ===\n";
$profile = DB::table('instructor_profiles')->where('user_id', $user->id)->first();
print_r($profile);

echo "\n=== COURSES AUDIT FOR USER ID {$user->id} AND PROFILE ID {$profile->id} ===\n";
$coursesByUser = DB::table('courses')->where('instructor_id', $user->id)->get();
$coursesByProfile = DB::table('courses')->where('instructor_id', $profile->id)->get();
echo "Courses with instructor_id = {$user->id}: " . $coursesByUser->count() . "\n";
echo "Courses with instructor_id = {$profile->id}: " . $coursesByProfile->count() . "\n";

echo "\n=== COURSE STATUSES IN DB ===\n";
$statuses = DB::table('courses')->selectRaw('status, COUNT(*) as count')->groupBy('status')->get();
print_r($statuses);

echo "\n=== ENROLLMENTS FOR USER'S COURSES ===\n";
$courseIds = $coursesByUser->pluck('id')->toArray();
$enrollmentsCount = DB::table('enrollments')->whereIn('course_id', $courseIds)->count();
$uniqueLearners = DB::table('enrollments')->whereIn('course_id', $courseIds)->distinct('user_id')->count('user_id');
echo "Total enrollments: $enrollmentsCount\n";
echo "Unique learners: $uniqueLearners\n";

echo "\n=== REVENUES FOR USER {$user->id} ===\n";
$revenuesCount = DB::table('revenues')->where('instructor_id', $user->id)->count();
$totalGross = DB::table('revenues')->where('instructor_id', $user->id)->sum('gross_amount');
$totalInstructorNet = DB::table('revenues')->where('instructor_id', $user->id)->sum('instructor_amount');
echo "Revenues rows: $revenuesCount\n";
echo "Total Gross: $totalGross\n";
echo "Total Instructor Net: $totalInstructorNet\n";

echo "\n=== ORDERS / ORDER ITEMS ===\n";
$ordersCount = DB::table('orders')->whereIn('course_id', $courseIds)->count();
echo "Orders count for instructor courses: $ordersCount\n";

echo "\n=== WITHDRAWALS ===\n";
if (Schema::hasTable('withdraw_requests')) {
    echo "Withdraw requests count: " . DB::table('withdraw_requests')->where('user_id', $user->id)->count() . "\n";
    print_r(DB::table('withdraw_requests')->where('user_id', $user->id)->get()->toArray());
}
