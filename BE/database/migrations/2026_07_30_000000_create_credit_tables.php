<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('course_credit_packages')) {
            Schema::create('course_credit_packages', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->integer('credits')->default(0);
                $table->decimal('price', 12, 2)->default(0);
                $table->string('status', 20)->default('active');
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('instructor_course_credits')) {
            Schema::create('instructor_course_credits', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('instructor_id')->unique();
                $table->integer('total_credits')->default(0);
                $table->integer('used_credits')->default(0);
                $table->integer('remaining_credits')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('instructor_credit_transactions')) {
            Schema::create('instructor_credit_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('instructor_id')->index();
                $table->unsignedBigInteger('order_id')->nullable()->index();
                $table->unsignedBigInteger('course_id')->nullable()->index();
                $table->string('type', 30);
                $table->integer('credits');
                $table->integer('balance_before')->default(0);
                $table->integer('balance_after')->default(0);
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('instructor_credit_transactions');
        Schema::dropIfExists('instructor_course_credits');
        Schema::dropIfExists('course_credit_packages');
    }
};
