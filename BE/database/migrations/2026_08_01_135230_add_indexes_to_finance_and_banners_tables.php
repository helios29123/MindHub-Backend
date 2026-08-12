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
        // 1. Users table
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('full_name', 'idx_users_full_name');
            });
        }

        // 2. Banners table
        if (Schema::hasTable('banners')) {
            Schema::table('banners', function (Blueprint $table) {
                $table->index(['position', 'status', 'sort_order'], 'idx_banners_pos_status_sort');
                $table->index('start_at', 'idx_banners_start_at');
                $table->index('end_at', 'idx_banners_end_at');
                $table->index('created_at', 'idx_banners_created_at');
            });
        }

        // 3. Payout Accounts table
        if (Schema::hasTable('payout_accounts')) {
            Schema::table('payout_accounts', function (Blueprint $table) {
                $table->index(['status', 'provider', 'created_at'], 'idx_payout_accounts_status_prov_created');
                $table->index('created_at', 'idx_payout_accounts_created_at');
            });
        }

        // 4. Withdraw Requests table
        if (Schema::hasTable('withdraw_requests')) {
            Schema::table('withdraw_requests', function (Blueprint $table) {
                $table->index(['status', 'created_at'], 'idx_withdraw_requests_status_created');
                $table->index('created_at', 'idx_withdraw_requests_created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('idx_users_full_name');
            });
        }

        if (Schema::hasTable('banners')) {
            Schema::table('banners', function (Blueprint $table) {
                $table->dropIndex('idx_banners_pos_status_sort');
                $table->dropIndex('idx_banners_start_at');
                $table->dropIndex('idx_banners_end_at');
                $table->dropIndex('idx_banners_created_at');
            });
        }

        if (Schema::hasTable('payout_accounts')) {
            Schema::table('payout_accounts', function (Blueprint $table) {
                $table->dropIndex('idx_payout_accounts_status_prov_created');
                $table->dropIndex('idx_payout_accounts_created_at');
            });
        }

        if (Schema::hasTable('withdraw_requests')) {
            Schema::table('withdraw_requests', function (Blueprint $table) {
                $table->dropIndex('idx_withdraw_requests_status_created');
                $table->dropIndex('idx_withdraw_requests_created_at');
            });
        }
    }
};
