<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_categories', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('category_id');
            $table->primary(['course_id', 'category_id']);
            $table->index(['category_id', 'course_id'], 'idx_course_categories_category');
            $table->foreign('category_id', 'fk_course_categories_category')->references('id')->on('categories')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('course_id', 'fk_course_categories_course')->references('id')->on('courses')->cascadeOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('course_categories');
    }
};