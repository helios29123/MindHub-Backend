<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_vietnamese_ci';

            $table->id();
            $table->unsignedBigInteger('course_section_id');
            $table->unsignedBigInteger('course_id');
            $table->string('title', 255);
            $table->enum('lesson_type', ['video', 'text', 'document'])->default('video');
            $table->longText('content')->nullable();
            $table->string('video_url', 2048)->nullable();
            $table->string('video_id', 255)->nullable()->unique('uq_lessons_video_id');
            $table->unsignedInteger('video_duration_seconds')->default(0);
            $table->boolean('is_preview')->default(false);
            $table->enum('status', ['draft', 'published', 'hidden'])->default('draft');
            $table->unsignedInteger('sort_order')->default(0);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->unique(['course_section_id', 'sort_order'], 'uq_lessons_section_order');
            $table->index('course_id', 'idx_lessons_course');
            $table->index(['course_section_id', 'course_id'], 'fk_lessons_section_course');
            $table->foreign(['course_section_id', 'course_id'], 'fk_lessons_section_course')->references(['id', 'course_id'])->on('course_sections')->cascadeOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};