<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function mh_table_exists(string $table): bool
{
    return Schema::hasTable($table);
}

function mh_has_column(string $table, string $column): bool
{
    return Schema::hasTable($table) && Schema::hasColumn($table, $column);
}

function mh_delete_by_user_ids(string $table, array $userIds): int
{
    if (! mh_table_exists($table) || ! mh_has_column($table, 'user_id') || empty($userIds)) {
        return 0;
    }

    return DB::table($table)->whereIn('user_id', $userIds)->delete();
}

DB::transaction(function (): void {
    $testEmails = [
        'instructor2@mindhub.test',
        'learner1@mindhub.test',
        'learner2@mindhub.test',
    ];

    $users = DB::table('users')
        ->whereIn('email', $testEmails)
        ->select('id', 'email', 'role')
        ->get();

    $userIds = $users
        ->pluck('id')
        ->map(fn ($id) => (int) $id)
        ->values()
        ->all();

    $instructorIds = $users
        ->where('role', 'instructor')
        ->pluck('id')
        ->map(fn ($id) => (int) $id)
        ->values()
        ->all();

    if (empty($userIds)) {
        dump('Không tìm thấy user test để reset.');
        return;
    }

    $orderIds = [];

    if (mh_table_exists('orders')) {
        $orderIds = DB::table('orders')
            ->whereIn('user_id', $userIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    if (mh_table_exists('instructor_credit_transactions') && mh_has_column('instructor_credit_transactions', 'order_id')) {
        $creditOrderIds = DB::table('instructor_credit_transactions')
            ->when(! empty($instructorIds) && mh_has_column('instructor_credit_transactions', 'instructor_id'), function ($query) use ($instructorIds) {
                $query->whereIn('instructor_id', $instructorIds);
            })
            ->whereNotNull('order_id')
            ->pluck('order_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $orderIds = array_values(array_unique(array_merge($orderIds, $creditOrderIds)));
    }

    /*
    |--------------------------------------------------------------------------
    | Xóa các bảng con đang khóa orders
    |--------------------------------------------------------------------------
    | Lỗi hiện tại của bạn là course_reviews.order_id đang FK tới orders.id.
    | Vì vậy phải xóa course_reviews trước orders.
    */
    if (! empty($orderIds)) {
        $orderChildTables = [
            'course_reviews',
            'revenues',
            'payments',
            'payment_logs',
            'order_items',
            'order_details',
            'coupon_usages',
            'transactions',
            'payment_transactions',
        ];

        foreach ($orderChildTables as $table) {
            if (mh_table_exists($table) && mh_has_column($table, 'order_id')) {
                DB::table($table)->whereIn('order_id', $orderIds)->delete();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Tự động tìm thêm bảng nào có FK tới orders.id rồi xóa theo FK column
        |--------------------------------------------------------------------------
        */
        $databaseName = DB::getDatabaseName();

        $foreignChildren = DB::select(
            "
            SELECT TABLE_NAME AS table_name, COLUMN_NAME AS column_name
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
              AND REFERENCED_TABLE_NAME = 'orders'
              AND REFERENCED_COLUMN_NAME = 'id'
            ",
            [$databaseName]
        );

        foreach ($foreignChildren as $child) {
            $table = $child->table_name;
            $column = $child->column_name;

            if (mh_table_exists($table) && mh_has_column($table, $column)) {
                DB::table($table)->whereIn($column, $orderIds)->delete();
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Xóa credit transactions
    |--------------------------------------------------------------------------
    */
    if (mh_table_exists('instructor_credit_transactions')) {
        DB::table('instructor_credit_transactions')
            ->where(function ($query) use ($instructorIds, $orderIds) {
                if (! empty($instructorIds) && mh_has_column('instructor_credit_transactions', 'instructor_id')) {
                    $query->whereIn('instructor_id', $instructorIds);
                }

                if (! empty($orderIds) && mh_has_column('instructor_credit_transactions', 'order_id')) {
                    $query->orWhereIn('order_id', $orderIds);
                }
            })
            ->delete();
    }

    /*
    |--------------------------------------------------------------------------
    | Xóa learning progress / note / quiz attempt
    |--------------------------------------------------------------------------
    */
    if (mh_table_exists('quiz_attempts') && mh_has_column('quiz_attempts', 'user_id')) {
        $attemptIds = DB::table('quiz_attempts')
            ->whereIn('user_id', $userIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if (! empty($attemptIds)) {
            foreach (['quiz_attempt_answers', 'quiz_answers'] as $table) {
                if (mh_table_exists($table) && mh_has_column($table, 'quiz_attempt_id')) {
                    DB::table($table)->whereIn('quiz_attempt_id', $attemptIds)->delete();
                }
            }
        }

        DB::table('quiz_attempts')->whereIn('user_id', $userIds)->delete();
    }

    foreach ([
        'lesson_notes',
        'lesson_progress',
        'video_progress',
        'course_progress',
        'learning_progress',
    ] as $table) {
        mh_delete_by_user_ids($table, $userIds);
    }

    /*
    |--------------------------------------------------------------------------
    | Xóa reviews theo user_id nếu bảng có user_id
    |--------------------------------------------------------------------------
    */
    foreach ([
        'course_reviews',
        'course_ratings',
        'ratings',
        'reviews',
    ] as $table) {
        mh_delete_by_user_ids($table, $userIds);
    }

    /*
    |--------------------------------------------------------------------------
    | Xóa certificates trước enrollments
    |--------------------------------------------------------------------------
    */
    $enrollmentIds = [];

    if (mh_table_exists('enrollments') && mh_has_column('enrollments', 'user_id')) {
        $enrollmentIds = DB::table('enrollments')
            ->whereIn('user_id', $userIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    if (! empty($enrollmentIds)) {
        foreach ([
            'certificates',
            'course_certificates',
        ] as $table) {
            if (mh_table_exists($table) && mh_has_column($table, 'enrollment_id')) {
                DB::table($table)->whereIn('enrollment_id', $enrollmentIds)->delete();
            }
        }
    }

    foreach ([
        'certificates',
        'course_certificates',
    ] as $table) {
        mh_delete_by_user_ids($table, $userIds);
    }

    if (mh_table_exists('enrollments') && mh_has_column('enrollments', 'user_id')) {
        DB::table('enrollments')->whereIn('user_id', $userIds)->delete();
    }

    /*
    |--------------------------------------------------------------------------
    | Xóa orders sau cùng
    |--------------------------------------------------------------------------
    */
    if (! empty($orderIds) && mh_table_exists('orders')) {
        DB::table('orders')->whereIn('id', $orderIds)->delete();
    }

    /*
    |--------------------------------------------------------------------------
    | Reset credit balance
    |--------------------------------------------------------------------------
    */
    if (mh_table_exists('instructor_course_credits') && ! empty($instructorIds)) {
        foreach ($instructorIds as $instructorId) {
            $data = [];

            if (mh_has_column('instructor_course_credits', 'total_credits')) {
                $data['total_credits'] = 0;
            }

            if (mh_has_column('instructor_course_credits', 'used_credits')) {
                $data['used_credits'] = 0;
            }

            if (mh_has_column('instructor_course_credits', 'remaining_credits')) {
                $data['remaining_credits'] = 0;
            }

            if (mh_has_column('instructor_course_credits', 'updated_at')) {
                $data['updated_at'] = now();
            }

            $exists = DB::table('instructor_course_credits')
                ->where('instructor_id', $instructorId)
                ->exists();

            if ($exists) {
                DB::table('instructor_course_credits')
                    ->where('instructor_id', $instructorId)
                    ->update($data);
            } else {
                $insertData = array_merge([
                    'instructor_id' => $instructorId,
                ], $data);

                if (mh_has_column('instructor_course_credits', 'created_at')) {
                    $insertData['created_at'] = now();
                }

                DB::table('instructor_course_credits')->insert($insertData);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Reset dấu đã trừ credit trên khóa học của instructor test
    |--------------------------------------------------------------------------
    */
    if (mh_table_exists('courses') && ! empty($instructorIds) && mh_has_column('courses', 'instructor_id')) {
        $courseUpdate = [];

        if (mh_has_column('courses', 'credit_used_at')) {
            $courseUpdate['credit_used_at'] = null;
        }

        if (mh_has_column('courses', 'credit_transaction_id')) {
            $courseUpdate['credit_transaction_id'] = null;
        }

        if (mh_has_column('courses', 'updated_at')) {
            $courseUpdate['updated_at'] = now();
        }

        if (! empty($courseUpdate)) {
            DB::table('courses')
                ->whereIn('instructor_id', $instructorIds)
                ->update($courseUpdate);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Revoke sessions để login lại sạch
    |--------------------------------------------------------------------------
    */
    if (mh_table_exists('sessions') && mh_has_column('sessions', 'user_id')) {
        $sessionUpdate = [];

        if (mh_has_column('sessions', 'revoked_at')) {
            $sessionUpdate['revoked_at'] = now();
        }

        if (mh_has_column('sessions', 'updated_at')) {
            $sessionUpdate['updated_at'] = now();
        }

        if (! empty($sessionUpdate)) {
            DB::table('sessions')
                ->whereIn('user_id', $userIds)
                ->update($sessionUpdate);
        }
    }

    dump([
        'reset_done' => true,
        'test_users' => $users,
        'order_ids_reset' => $orderIds,
        'instructor_ids_reset' => $instructorIds,
    ]);
});