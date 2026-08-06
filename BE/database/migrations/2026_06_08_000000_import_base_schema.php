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
            $paths = [
                database_path('sql/elearning_erd_full_with_notebooklm_video_seed.sql'),
                base_path('../elearning_erd_full_with_notebooklm_video_seed.sql'),
            ];
            foreach ($paths as $sqlPath) {
                if (file_exists($sqlPath)) {
                    DB::unprepared(file_get_contents($sqlPath));
                    break;
                }
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
