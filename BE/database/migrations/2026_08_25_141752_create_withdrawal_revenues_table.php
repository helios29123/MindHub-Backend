<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawal_revenues', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_vietnamese_ci';

            $table->unsignedBigInteger('withdrawal_id');
            $table->unsignedBigInteger('revenue_id');
            $table->decimal('allocated_amount', 15, 2);
            $table->dateTime('created_at')->useCurrent();
            $table->primary(['withdrawal_id', 'revenue_id']);
            $table->index('revenue_id', 'idx_withdrawal_revenues_revenue');
            $table->foreign('revenue_id', 'fk_withdrawal_revenues_revenue')->references('id')->on('revenues')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('withdrawal_id', 'fk_withdrawal_revenues_withdrawal')->references('id')->on('withdraw_requests')->cascadeOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_revenues');
    }
};