<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_reviews', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_vietnamese_ci';

            $table->id();
            $table->unsignedBigInteger('order_id')->unique('uq_course_reviews_order');
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->dateTime('edited_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->foreign('order_id', 'fk_course_reviews_order')->references('id')->on('orders')->restrictOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('course_reviews');
    }
};