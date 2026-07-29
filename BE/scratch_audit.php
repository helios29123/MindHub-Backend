<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== 1. INSTRUCTOR PROFILES ===\n";
if (Schema::hasTable('instructor_profiles')) {
    print_r(Schema::getColumnListing('instructor_profiles'));
    print_r(DB::table('instructor_profiles')->get()->toArray());
}

echo "\n=== 2. COURSES INSTRUCTOR_ID BREAKDOWN ===\n";
print_r(DB::select("SELECT instructor_id, status, COUNT(*) as count FROM courses GROUP BY instructor_id, status"));

echo "\n=== 3. COURSES FOR USER 6 / INSTRUCTOR PROFILES ===\n";
print_r(DB::select("SELECT id, title, status, instructor_id FROM courses WHERE instructor_id IN (1, 6) LIMIT 30"));

echo "\n=== 4. ENROLLMENTS ===\n";
if (Schema::hasTable('enrollments')) {
    print_r(Schema::getColumnListing('enrollments'));
    print_r(DB::select("SELECT course_id, COUNT(*) as count FROM enrollments GROUP BY course_id"));
    print_r(DB::select("SELECT * FROM enrollments LIMIT 10"));
}

echo "\n=== 5. ORDERS & ORDER ITEMS ===\n";
if (Schema::hasTable('orders')) {
    print_r(Schema::getColumnListing('orders'));
    print_r(DB::select("SELECT * FROM orders LIMIT 5"));
}
if (Schema::hasTable('order_items')) {
    print_r(Schema::getColumnListing('order_items'));
    print_r(DB::select("SELECT * FROM order_items LIMIT 5"));
}

echo "\n=== 6. REVENUE / TRANSACTIONS / EARNINGS / PAYOUTS ===\n";
$revTables = ['instructor_revenues', 'revenues', 'transactions', 'payouts', 'instructor_payouts', 'withdrawals', 'instructor_withdrawals', 'earnings', 'instructor_earnings'];
foreach ($revTables as $t) {
    if (Schema::hasTable($t)) {
        echo "--- Table $t ---\n";
        print_r(Schema::getColumnListing($t));
        print_r(DB::table($t)->limit(5)->get()->toArray());
    }
}
