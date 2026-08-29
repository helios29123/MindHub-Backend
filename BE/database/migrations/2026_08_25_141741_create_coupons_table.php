<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_vietnamese_ci';

            $table->id();
            $table->string('code', 80)->unique('uq_coupons_code');
            $table->unsignedBigInteger('course_id');
            $table->enum('campaign_type', ['discount', 'trial'])->default('discount');
            $table->enum('discount_type', ['percent', 'fixed'])->nullable();
            $table->decimal('discount_value', 15, 2)->nullable();
            $table->decimal('max_discount_amount', 15, 2)->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->enum('status', ['scheduled', 'active', 'inactive', 'expired', 'used_up'])->default('active');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['course_id', 'status'], 'idx_coupons_course_status');
            $table->index(['course_id', 'start_at', 'end_at'], 'idx_coupons_course_window');
            $table->index(['campaign_type', 'created_at'], 'idx_coupons_campaign_created');
            $table->foreign('course_id', 'fk_coupons_course')
                ->references('id')->on('courses')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
