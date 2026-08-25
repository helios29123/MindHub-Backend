<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_rules', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_vietnamese_ci';

            $table->id();
            $table->string('name', 150);
            $table->string('description', 1000)->nullable();
            $table->decimal('instructor_rate', 5, 4);
            $table->decimal('platform_rate', 5, 4);
            $table->boolean('is_active')->default(false);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->index('is_active', 'idx_commission_rules_active');
        });

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_commission_rules_one_active_bi`
BEFORE INSERT ON `commission_rules`
FOR EACH ROW
BEGIN
    IF NEW.is_active = 1
       AND EXISTS (SELECT 1 FROM `commission_rules` WHERE `is_active` = 1)
    THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Only one commission rule can be active at a time';
    END IF;
END
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_commission_rules_one_active_bu`
BEFORE UPDATE ON `commission_rules`
FOR EACH ROW
BEGIN
    IF NEW.is_active = 1
       AND EXISTS (
            SELECT 1
            FROM `commission_rules`
            WHERE `is_active` = 1
              AND `id` <> OLD.`id`
       )
    THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Only one commission rule can be active at a time';
    END IF;

    IF (NEW.instructor_rate <> OLD.instructor_rate
        OR NEW.platform_rate <> OLD.platform_rate)
       AND (
            EXISTS (SELECT 1 FROM `orders` WHERE `commission_rule_id` = OLD.`id`)
            OR EXISTS (SELECT 1 FROM `revenues` WHERE `commission_rule_id` = OLD.`id`)
       )
    THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Referenced commission rule rates are immutable; create a new rule instead';
    END IF;
END
SQL);

    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_commission_rules_one_active_bu`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_commission_rules_one_active_bi`');
        Schema::dropIfExists('commission_rules');
    }
};