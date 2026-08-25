<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlist', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('course_id');
            $table->dateTime('created_at')->useCurrent();
            $table->primary(['user_id', 'course_id']);
            $table->index('course_id', 'idx_wishlist_course');
            $table->foreign('course_id', 'fk_wishlist_course')->references('id')->on('courses')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('user_id', 'fk_wishlist_user')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('wishlist');
    }
};