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
        if (!Schema::hasTable('course_views')) {
            Schema::create('course_views', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('course_id')->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('session_id', 255)->nullable()->index();
                $table->string('ip_hash', 64)->nullable();
                $table->string('user_agent_hash', 64)->nullable();
                $table->timestamp('viewed_at')->useCurrent();
                $table->timestamps();

                $table->index(['course_id', 'viewed_at']);
                $table->index(['user_id', 'course_id']);
                $table->index(['session_id', 'course_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_views');
    }
};
