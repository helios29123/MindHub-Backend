<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_vietnamese_ci';

            $table->id();
            $table->string('order_code', 80)->unique('uq_orders_order_code');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->unsignedBigInteger('commission_rule_id');
            $table->enum('status', ['pending_payment', 'paid', 'cancelled', 'failed', 'expired'])->default('pending_payment');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'expired'])->default('pending');
            $table->decimal('price_snapshot', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 50)->nullable();
            $table->string('provider_transaction_id', 191)->nullable()->unique('uq_orders_provider_transaction');
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->string('cancelled_reason', 1000)->nullable();
            $table->string('failed_reason', 1000)->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->index(['user_id', 'status'], 'idx_orders_user_status');
            $table->index('course_id', 'idx_orders_course');
            $table->index('coupon_id', 'idx_orders_coupon');
            $table->index('commission_rule_id', 'idx_orders_commission_rule');
            $table->index(['payment_status', 'created_at'], 'idx_orders_payment_status');
            $table->foreign('commission_rule_id', 'fk_orders_commission_rule')->references('id')->on('commission_rules')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('coupon_id', 'fk_orders_coupon')->references('id')->on('coupons')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('course_id', 'fk_orders_course')->references('id')->on('courses')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('user_id', 'fk_orders_user')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};