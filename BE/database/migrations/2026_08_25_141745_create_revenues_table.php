<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revenues', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_vietnamese_ci';

            $table->id();
            $table->unsignedBigInteger('instructor_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('order_id')->unique('uq_revenues_order');
            $table->decimal('gross_amount', 15, 2);
            $table->decimal('instructor_amount', 15, 2);
            $table->decimal('platform_fee_amount', 15, 2);
            $table->unsignedBigInteger('commission_rule_id');
            $table->dateTime('earned_at')->useCurrent();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->index('instructor_id', 'idx_revenues_instructor_status');
            $table->index('course_id', 'idx_revenues_course');
            $table->index('commission_rule_id', 'idx_revenues_commission_rule');
            $table->foreign('commission_rule_id', 'fk_revenues_commission_rule')->references('id')->on('commission_rules')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('course_id', 'fk_revenues_course')->references('id')->on('courses')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('instructor_id', 'fk_revenues_instructor')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('order_id', 'fk_revenues_order')->references('id')->on('orders')->restrictOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('revenues');
    }
};