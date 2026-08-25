<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payout_accounts', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_vietnamese_ci';

            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('provider', 100);
            $table->string('account_number', 100);
            $table->string('account_name', 255);
            $table->enum('status', ['pending_verification', 'verified', 'disabled'])->default('pending_verification');
            $table->boolean('is_default')->default(false);
            $table->dateTime('verified_at')->nullable();
            $table->dateTime('disabled_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->unique(['user_id', 'provider', 'account_number'], 'uq_payout_accounts_user_provider_account');
            $table->index(['user_id', 'status'], 'idx_payout_accounts_user_status');
            $table->foreign('user_id', 'fk_payout_accounts_user')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
        });

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_payout_accounts_default_bi`
BEFORE INSERT ON `payout_accounts`
FOR EACH ROW
BEGIN
    IF NEW.is_default = 1 AND NEW.status <> 'verified' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Only a verified payout account can be default';
    END IF;

    IF NEW.is_default = 1
       AND EXISTS (
            SELECT 1
            FROM `payout_accounts`
            WHERE `user_id` = NEW.`user_id`
              AND `is_default` = 1
       )
    THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'A user can have only one default payout account';
    END IF;
END
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_payout_accounts_default_bu`
BEFORE UPDATE ON `payout_accounts`
FOR EACH ROW
BEGIN
    IF NEW.is_default = 1 AND NEW.status <> 'verified' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Only a verified payout account can be default';
    END IF;

    IF NEW.is_default = 1
       AND EXISTS (
            SELECT 1
            FROM `payout_accounts`
            WHERE `user_id` = NEW.`user_id`
              AND `is_default` = 1
              AND `id` <> OLD.`id`
       )
    THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'A user can have only one default payout account';
    END IF;
END
SQL);

    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_payout_accounts_default_bu`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_payout_accounts_default_bi`');
        Schema::dropIfExists('payout_accounts');
    }
};