<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name', 150);
            $table->string('slug', 180)->unique('uq_categories_slug');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->index('parent_id', 'idx_categories_parent');
            $table->foreign('parent_id', 'fk_categories_parent')->references('id')->on('categories')->nullOnDelete()->cascadeOnUpdate();
        });

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_categories_two_levels_bi`
BEFORE INSERT ON `categories`
FOR EACH ROW
BEGIN
    IF NEW.parent_id IS NOT NULL
       AND EXISTS (
            SELECT 1
            FROM `categories`
            WHERE id = NEW.parent_id
              AND parent_id IS NOT NULL
       )
    THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Categories are limited to two levels';
    END IF;
END
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_categories_two_levels_bu`
BEFORE UPDATE ON `categories`
FOR EACH ROW
BEGIN
    IF NEW.parent_id IS NOT NULL
       AND EXISTS (
            SELECT 1
            FROM `categories`
            WHERE id = NEW.parent_id
              AND parent_id IS NOT NULL
       )
    THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Categories are limited to two levels';
    END IF;
END
SQL);

    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_categories_two_levels_bu`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_categories_two_levels_bi`');
        Schema::dropIfExists('categories');
    }
};