<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_faqs', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->unsignedBigInteger('faq_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedInteger('sort_order')->default(0);
            $table->primary(['faq_id', 'course_id']);
            $table->index(['course_id', 'sort_order'], 'idx_course_faqs_course_order');
            $table->foreign('course_id', 'fk_course_faqs_course')->references('id')->on('courses')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('faq_id', 'fk_course_faqs_faq')->references('id')->on('faqs')->cascadeOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('course_faqs');
    }
};