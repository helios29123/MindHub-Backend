<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('revenues')) {
            // Drop check constraint if present
            try {
                DB::statement("ALTER TABLE revenues DROP CHECK chk_revenues_status");
            } catch (\Throwable $e) {
                // Ignore if constraint does not exist
            }

            Schema::table('revenues', function (Blueprint $table) {
                if (!Schema::hasColumn('revenues', 'available_at')) {
                    $table->timestamp('available_at')->nullable()->after('earned_at');
                }
                if (!Schema::hasColumn('revenues', 'payout_id')) {
                    $table->foreignId('payout_id')->nullable()->after('order_id')->index();
                }
            });
        }

        if (Schema::hasTable('withdraw_requests')) {
            // Drop check constraint if present
            try {
                DB::statement("ALTER TABLE withdraw_requests DROP CHECK chk_withdraw_requests_status");
            } catch (\Throwable $e) {
                // Ignore if constraint does not exist
            }

            // Make payout_account_id, account_number_snapshot, account_name_snapshot nullable
            try {
                DB::statement("ALTER TABLE withdraw_requests MODIFY payout_account_id BIGINT UNSIGNED NULL");
            } catch (\Throwable $e) {}

            try {
                DB::statement("ALTER TABLE withdraw_requests MODIFY account_number_snapshot VARCHAR(255) NULL");
            } catch (\Throwable $e) {}

            try {
                DB::statement("ALTER TABLE withdraw_requests MODIFY account_name_snapshot VARCHAR(255) NULL");
            } catch (\Throwable $e) {}

            Schema::table('withdraw_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('withdraw_requests', 'period_start')) {
                    $table->date('period_start')->nullable()->after('payout_account_id');
                }
                if (!Schema::hasColumn('withdraw_requests', 'period_end')) {
                    $table->date('period_end')->nullable()->after('period_start');
                }
                if (!Schema::hasColumn('withdraw_requests', 'expected_payment_at')) {
                    $table->timestamp('expected_payment_at')->nullable()->after('requested_at');
                }
                if (!Schema::hasColumn('withdraw_requests', 'processed_at')) {
                    $table->timestamp('processed_at')->nullable()->after('paid_at');
                }
                if (!Schema::hasColumn('withdraw_requests', 'bank_name')) {
                    $table->string('bank_name', 100)->nullable()->after('account_name_snapshot');
                }
                if (!Schema::hasColumn('withdraw_requests', 'payout_method')) {
                    $table->string('payout_method', 50)->nullable()->default('bank_transfer')->after('bank_name');
                }
                if (!Schema::hasColumn('withdraw_requests', 'blocked_reason')) {
                    $table->string('blocked_reason', 255)->nullable()->after('rejected_reason');
                }
                if (!Schema::hasColumn('withdraw_requests', 'failure_reason')) {
                    $table->string('failure_reason', 255)->nullable()->after('blocked_reason');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('revenues')) {
            Schema::table('revenues', function (Blueprint $table) {
                if (Schema::hasColumn('revenues', 'available_at')) {
                    $table->dropColumn('available_at');
                }
                if (Schema::hasColumn('revenues', 'payout_id')) {
                    $table->dropColumn('payout_id');
                }
            });
        }

        if (Schema::hasTable('withdraw_requests')) {
            Schema::table('withdraw_requests', function (Blueprint $table) {
                $columns = [
                    'period_start', 'period_end', 'expected_payment_at', 'processed_at',
                    'bank_name', 'payout_method', 'blocked_reason', 'failure_reason'
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('withdraw_requests', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
