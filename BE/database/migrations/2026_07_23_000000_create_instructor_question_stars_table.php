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
        if (!Schema::hasTable('instructor_question_stars')) {
            Schema::create('instructor_question_stars', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('instructor_id');
                $table->unsignedBigInteger('comment_id');
                $table->timestamps();

                $table->foreign('instructor_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('comment_id')->references('id')->on('comments')->onDelete('cascade');
                $table->unique(['instructor_id', 'comment_id']);
            });
        }

        if (Schema::hasTable('comments') && !Schema::hasColumn('comments', 'is_official')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->boolean('is_official')->default(true)->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructor_question_stars');
        if (Schema::hasTable('comments') && Schema::hasColumn('comments', 'is_official')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->dropColumn('is_official');
            });
        }
    }
};
