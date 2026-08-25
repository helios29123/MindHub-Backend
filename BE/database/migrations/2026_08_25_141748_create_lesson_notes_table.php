<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_notes', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->unsignedBigInteger('enrollment_id');
            $table->unsignedBigInteger('lesson_id');
            $table->text('content');
            $table->unsignedInteger('note_time_second')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->index(['enrollment_id', 'lesson_id'], 'idx_lesson_notes_enrollment_lesson');
            $table->index('lesson_id', 'fk_lesson_notes_lesson');
            $table->foreign('enrollment_id', 'fk_lesson_notes_enrollment')->references('id')->on('enrollments')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('lesson_id', 'fk_lesson_notes_lesson')->references('id')->on('lessons')->cascadeOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_notes');
    }
};