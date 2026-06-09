<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('courses')) {
            return;
        }

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->string('thumbnail_url', 500)->nullable();
            $table->string('intro_video_url', 500)->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->string('level', 50)->nullable();
            $table->string('language', 20)->default('vi');
            $table->text('requirements')->nullable();
            $table->text('outcomes')->nullable();
            $table->enum('status', [
                'draft',
                'pending_review',
                'approved',
                'rejected',
                'published',
                'hidden',
            ])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('total_duration_seconds')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->text('admin_reject_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'is_featured']);
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
