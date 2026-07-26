<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('withdraw_requests')) {
            Schema::table('withdraw_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('withdraw_requests', 'type')) {
                    $table->string('type', 30)->default('automatic_payout')->after('status')->index();
                }
                if (!Schema::hasColumn('withdraw_requests', 'rejection_reason')) {
                    $table->string('rejection_reason', 255)->nullable()->after('blocked_reason');
                }
            });
        }

        if (!Schema::hasTable('withdrawal_revenues')) {
            Schema::create('withdrawal_revenues', function (Blueprint $table) {
                $table->id();
                $table->foreignId('withdrawal_id')->constrained('withdraw_requests')->onDelete('cascade');
                $table->foreignId('revenue_id')->constrained('revenues')->onDelete('cascade');
                $table->decimal('allocated_amount', 15, 2);
                $table->timestamps();

                $table->unique(['withdrawal_id', 'revenue_id']);
                $table->index('revenue_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_revenues');

        if (Schema::hasTable('withdraw_requests')) {
            Schema::table('withdraw_requests', function (Blueprint $table) {
                if (Schema::hasColumn('withdraw_requests', 'type')) {
                    $table->dropColumn('type');
                }
                if (Schema::hasColumn('withdraw_requests', 'rejection_reason')) {
                    $table->dropColumn('rejection_reason');
                }
            });
        }
    }
};
