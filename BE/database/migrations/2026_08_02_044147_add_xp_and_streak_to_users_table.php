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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('xp')->default(0)->after('status');
            $table->unsignedInteger('streak_count')->default(0)->after('xp');
            $table->date('last_active_date')->nullable()->after('streak_count');
            $table->date('last_mission_completed_date')->nullable()->after('last_active_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['xp', 'streak_count', 'last_active_date', 'last_mission_completed_date']);
        });
    }
};
