<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function firstId(string $table, ?callable $callback = null): ?int
{
    if (! Schema::hasTable($table)) {
        return null;
    }

    $query = DB::table($table)->select('id')->orderBy('id');

    if ($callback) {
        $callback($query);
    }

    $row = $query->first();

    return $row ? (int) $row->id : null;
}

$result = [
    'course_id' => firstId('courses'),
    'pending_course_id' => firstId('courses', function ($q) {
        $q->where('status', 'pending_review');
    }),
    'order_id' => firstId('orders'),
    'revenue_id' => firstId('revenues'),
    'payout_batch_id' => firstId('payout_batches'),
    'payout_item_id' => firstId('payout_items'),
    'payout_account_id' => firstId('payout_accounts'),
    'user_id' => firstId('users', function ($q) {
        $q->where('role', '!=', 'admin');
    }),
    'instructor_user_id' => firstId('users', function ($q) {
        $q->where('role', 'instructor');
    }),
    'commission_rule_id' => firstId('commission_rules'),
    'notification_id' => firstId('notifications'),
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);