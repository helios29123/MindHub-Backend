<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // If comments table is missing (which happens during migrate:fresh), import the base SQL dump
        if (!Schema::hasTable('comments')) {
            $sqlPath = database_path('sql/elearning_erd_full_with_notebooklm_video_seed.sql');
            if (file_exists($sqlPath)) {
                DB::unprepared(file_get_contents($sqlPath));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollbacks needed for base SQL import
    }
};
