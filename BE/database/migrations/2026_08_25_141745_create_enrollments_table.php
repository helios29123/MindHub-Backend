<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_vietnamese_ci';

            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('order_id')->unique('uq_enrollments_order');
            $table->enum('status', ['active', 'completed', 'inactive'])->default('active');
            $table->decimal('progress_percent', 5, 2)->default(0);
            $table->dateTime('enrolled_at')->useCurrent();
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('last_accessed_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->unique(['user_id', 'course_id'], 'uq_enrollments_user_course');
            $table->index(['course_id', 'status'], 'idx_enrollments_course_status');
            $table->foreign('course_id', 'fk_enrollments_course')->references('id')->on('courses')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('order_id', 'fk_enrollments_order')->references('id')->on('orders')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('user_id', 'fk_enrollments_user')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};