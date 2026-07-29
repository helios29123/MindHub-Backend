<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('revenues')) {
            return;
        }

        Schema::create('revenues', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('course_id')
                ->constrained('courses')
                ->cascadeOnDelete();

            $table->foreignId('instructor_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->decimal('gross_amount', 15, 2)->default(0);
            $table->decimal('instructor_amount', 15, 2)->default(0);
            $table->decimal('platform_fee_amount', 15, 2)->default(0);

            $table->string('status', 30)->default('available');
            $table->timestamp('earned_at')->nullable();

            $table->timestamps();

            $table->unique('order_id');
            $table->index(['instructor_id', 'status']);
            $table->index(['course_id', 'status']);
            $table->index(['earned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revenues');
    }
};