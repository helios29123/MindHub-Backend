<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdraw_requests', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_vietnamese_ci';

            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('payout_account_id');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'approved', 'processing', 'manual_required', 'paid', 'rejected', 'cancelled', 'failed'])->default('pending')->comment('pending=chờ duyệt; approved=admin đã duyệt; processing=đang chi trả; manual_required=cần admin xử lý thủ công; paid=đã thanh toán; rejected=admin từ chối; cancelled=giảng viên tự hủy khi còn pending; failed=chi trả thất bại cuối cùng');
            $table->dateTime('requested_at')->useCurrent();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('processed_at')->nullable();
            $table->string('provider_payout_id', 191)->nullable()->unique('uq_withdraw_provider_payout_id');
            $table->string('failure_reason', 1000)->nullable();
            $table->string('rejected_reason', 1000)->nullable();
            $table->string('admin_note', 1000)->nullable();
            $table->string('account_number_snapshot', 100);
            $table->string('account_name_snapshot', 255);
            $table->decimal('available_balance_before', 15, 2);
            $table->decimal('available_balance_after', 15, 2);
            $table->string('bank_name_snapshot', 255);
            $table->string('payout_provider', 100)->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->index(['user_id', 'status', 'requested_at'], 'idx_withdraw_user_status');
            $table->index('payout_account_id', 'idx_withdraw_payout_account');
            $table->foreign('payout_account_id', 'fk_withdraw_payout_account')->references('id')->on('payout_accounts')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('user_id', 'fk_withdraw_user')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('withdraw_requests');
    }
};