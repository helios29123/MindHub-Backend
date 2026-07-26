<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. users
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'locked_reason')) {
                    $table->text('locked_reason')->nullable();
                }
                if (!Schema::hasColumn('users', 'locked_at')) {
                    $table->timestamp('locked_at')->nullable();
                }
            });
        }

        // 2. courses
        if (Schema::hasTable('courses')) {
            Schema::table('courses', function (Blueprint $table) {
                if (!Schema::hasColumn('courses', 'admin_reject_reason')) {
                    $table->text('admin_reject_reason')->nullable();
                }
                if (!Schema::hasColumn('courses', 'reviewed_at')) {
                    $table->timestamp('reviewed_at')->nullable();
                }
                if (!Schema::hasColumn('courses', 'reviewed_by')) {
                    $table->unsignedBigInteger('reviewed_by')->nullable();
                }
                if (!Schema::hasColumn('courses', 'is_featured')) {
                    $table->boolean('is_featured')->default(false);
                }
            });
        }

        // 3. payout_accounts
        if (Schema::hasTable('payout_accounts')) {
            Schema::table('payout_accounts', function (Blueprint $table) {
                if (!Schema::hasColumn('payout_accounts', 'reject_reason')) {
                    $table->text('reject_reason')->nullable();
                }
                if (!Schema::hasColumn('payout_accounts', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable();
                }
                if (!Schema::hasColumn('payout_accounts', 'rejected_at')) {
                    $table->timestamp('rejected_at')->nullable();
                }
                if (!Schema::hasColumn('payout_accounts', 'disabled_at')) {
                    $table->timestamp('disabled_at')->nullable();
                }
            });
        }

        // 4. withdraw_requests
        if (Schema::hasTable('withdraw_requests')) {
            Schema::table('withdraw_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('withdraw_requests', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable();
                }
                if (!Schema::hasColumn('withdraw_requests', 'rejected_at')) {
                    $table->timestamp('rejected_at')->nullable();
                }
                if (!Schema::hasColumn('withdraw_requests', 'paid_at')) {
                    $table->timestamp('paid_at')->nullable();
                }
                if (!Schema::hasColumn('withdraw_requests', 'rejected_reason')) {
                    $table->text('rejected_reason')->nullable();
                }
                if (!Schema::hasColumn('withdraw_requests', 'admin_note')) {
                    $table->text('admin_note')->nullable();
                }
            });
        }

        // 5. orders
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'cancelled_reason')) {
                    $table->text('cancelled_reason')->nullable();
                }
                if (!Schema::hasColumn('orders', 'failed_reason')) {
                    $table->text('failed_reason')->nullable();
                }
                if (!Schema::hasColumn('orders', 'provider_transaction_id')) {
                    $table->string('provider_transaction_id')->nullable();
                }
                if (!Schema::hasColumn('orders', 'paid_at')) {
                    $table->timestamp('paid_at')->nullable();
                }
            });
        }

        // 6. banners
        if (Schema::hasTable('banners')) {
            Schema::table('banners', function (Blueprint $table) {
                if (!Schema::hasColumn('banners', 'deleted_at')) {
                    $table->softDeletes();
                }
                if (!Schema::hasColumn('banners', 'sort_order')) {
                    $table->integer('sort_order')->default(0);
                }
                if (!Schema::hasColumn('banners', 'status')) {
                    $table->string('status')->default('active');
                }
            });
        }

        // 7. faqs
        if (Schema::hasTable('faqs')) {
            Schema::table('faqs', function (Blueprint $table) {
                if (!Schema::hasColumn('faqs', 'deleted_at')) {
                    $table->softDeletes();
                }
                if (!Schema::hasColumn('faqs', 'sort_order')) {
                    $table->integer('sort_order')->default(0);
                }
                if (!Schema::hasColumn('faqs', 'status')) {
                    $table->string('status')->default('active');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rolling back specific column insertions is recommended in multi-table migrations
    }
};
