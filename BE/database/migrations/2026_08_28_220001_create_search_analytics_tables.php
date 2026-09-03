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
        if (!Schema::hasTable('search_logs')) {
            Schema::create('search_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('session_id', 64)->index();
                $table->string('query', 255);
                $table->string('normalized_query', 255)->index();
                $table->string('search_type', 32)->default('semantic_vector');
                $table->unsignedInteger('results_count')->default(0);
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();

                $table->index(['normalized_query', 'created_at']);
                $table->index('created_at');
            });
        }

        if (!Schema::hasTable('search_clicks')) {
            Schema::create('search_clicks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('search_log_id')->nullable()->constrained('search_logs')->onDelete('cascade');
                $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
                $table->string('query', 255)->nullable()->index();
                $table->unsignedInteger('clicked_position')->default(1)->comment('1-indexed rank position in search results');
                $table->unsignedInteger('time_to_click_seconds')->nullable()->comment('Seconds between search and click');
                $table->timestamps();

                $table->index(['course_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_clicks');
        Schema::dropIfExists('search_logs');
    }
};
