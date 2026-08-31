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
        Schema::table('course_reviews', function (Blueprint $table) {
            $table->index(['rating', 'id'], 'idx_course_reviews_rating_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index(['status', 'sort_order', 'id'], 'idx_categories_status_sort_id');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->index(['status', 'published_at'], 'idx_courses_status_published_at');
            $table->index(['status', 'price', 'sale_price'], 'idx_courses_status_price_sale');
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->index(['enrolled_at', 'status'], 'idx_enrollments_enrolled_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropIndex('idx_enrollments_enrolled_status');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex('idx_courses_status_published_at');
            $table->dropIndex('idx_courses_status_price_sale');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('idx_categories_status_sort_id');
        });

        Schema::table('course_reviews', function (Blueprint $table) {
            $table->dropIndex('idx_course_reviews_rating_id');
        });
    }
};
