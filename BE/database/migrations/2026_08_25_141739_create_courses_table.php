<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_vietnamese_ci';

            $table->id();
            $table->unsignedBigInteger('instructor_id');
            $table->string('title', 255);
            $table->string('slug', 255)->unique('uq_courses_slug');
            $table->string('short_description', 500)->nullable();
            $table->longText('description')->nullable();
            $table->string('thumbnail_url', 2048)->nullable();
            $table->string('thumbnail_public_id', 255)->nullable()->unique('uq_courses_thumbnail_public_id');
            $table->string('intro_video_url', 2048)->nullable();
            $table->string('intro_video_id', 255)->nullable()->unique('uq_courses_intro_video_id');
            $table->decimal('price', 15, 2)->default(0);
            $table->enum('course_level', ['beginner', 'intermediate', 'advanced', 'all_levels'])->default('beginner');
            $table->string('language', 20)->default('vi');
            $table->json('requirements')->nullable();
            $table->json('outcomes')->nullable();
            $table->enum('status', ['draft', 'pending_review', 'approved', 'rejected', 'published', 'hidden'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->dateTime('published_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->string('admin_reject_reason', 1000)->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->decimal('sale_price', 15, 2)->nullable();
            
            $table->index(['instructor_id', 'status'], 'idx_courses_instructor_status');
            $table->index(['is_featured', 'status'], 'idx_courses_featured');
            $table->index('reviewed_by', 'idx_courses_reviewed_by');
            $table->foreign('instructor_id', 'fk_courses_instructor')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('reviewed_by', 'fk_courses_reviewed_by')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};