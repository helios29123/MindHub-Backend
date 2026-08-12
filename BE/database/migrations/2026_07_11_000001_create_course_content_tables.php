<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('course_sections')) {
            Schema::create('course_sections', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
                $table->string('title', 255);
                $table->text('description')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->string('status', 30)->default('draft');
                $table->timestamps();
                $table->softDeletes();

                $table->index(['course_id', 'sort_order']);
                $table->index(['status']);
            });
        }

        if (!Schema::hasTable('lessons')) {
            Schema::create('lessons', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('course_section_id')->nullable()->constrained('course_sections')->nullOnDelete();
                $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
                $table->string('title', 255);
                $table->string('slug', 255);
                $table->string('lesson_type', 30)->default('video');
                $table->longText('content')->nullable();
                $table->string('video_url', 500)->nullable();
                $table->unsignedInteger('video_duration_seconds')->default(0);
                $table->boolean('is_preview')->default(false);
                $table->string('status', 30)->default('draft');
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['course_id', 'slug']);
                $table->index(['course_id', 'course_section_id']);
                $table->index(['course_section_id', 'sort_order']);
                $table->index(['status', 'lesson_type']);
                $table->index(['is_preview']);
            });
        }

        if (!Schema::hasTable('lesson_assets')) {
            Schema::create('lesson_assets', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnDelete();
                $table->string('title', 255);
                $table->string('file_url', 500);
                $table->string('file_name', 255)->nullable();
                $table->string('file_type', 50)->nullable();
                $table->unsignedBigInteger('file_size')->default(0);
                $table->text('note')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->softDeletes();

                $table->index(['lesson_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_assets');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('course_sections');
    }
};