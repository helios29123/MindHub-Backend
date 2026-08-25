<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_progress', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->unsignedBigInteger('enrollment_id');
            $table->unsignedBigInteger('lesson_id');
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->unsignedInteger('learning_duration_seconds')->default(0);
            $table->dateTime('last_accessed_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->unique(['enrollment_id', 'lesson_id'], 'uq_lesson_progress_enrollment_lesson');
            $table->index('lesson_id', 'idx_lesson_progress_lesson');
            $table->foreign('enrollment_id', 'fk_lesson_progress_enrollment')->references('id')->on('enrollments')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('lesson_id', 'fk_lesson_progress_lesson')->references('id')->on('lessons')->cascadeOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_progress');
    }
};