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
            if (!Schema::hasColumn('withdraw_requests', 'provider_payout_id')) {
                $table->string('provider_payout_id')->nullable()->after('payout_provider');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdraw_requests', function (Blueprint $table) {
            if (Schema::hasColumn('withdraw_requests', 'provider_payout_id')) {
                $table->dropColumn('provider_payout_id');
            }
        });
    }
};
