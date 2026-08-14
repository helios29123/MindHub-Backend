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
        Schema::table('withdraw_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('withdraw_requests', 'available_balance_before')) {
                $table->decimal('available_balance_before', 12, 2)->nullable()->after('amount');
            }
            if (!Schema::hasColumn('withdraw_requests', 'available_balance_after')) {
                $table->decimal('available_balance_after', 12, 2)->nullable()->after('available_balance_before');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdraw_requests', function (Blueprint $table) {
            if (Schema::hasColumn('withdraw_requests', 'available_balance_after')) {
                $table->dropColumn('available_balance_after');
            }
            if (Schema::hasColumn('withdraw_requests', 'available_balance_before')) {
                $table->dropColumn('available_balance_before');
            }
        });
    }
};
