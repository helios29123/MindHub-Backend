<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders')) {
            return;
        }

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('order_code', 100)->unique();
            $table->enum('status', ['pending', 'paid', 'cancelled', 'failed', 'refunded'])->default('pending');
            $table->decimal('price_snapshot', 12, 2)->default(0);
            $table->string('payment_method', 100)->nullable();
            $table->string('payment_status', 100)->nullable();
            $table->string('provider_transaction_id', 255)->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'course_id']);
            $table->index(['status', 'payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
