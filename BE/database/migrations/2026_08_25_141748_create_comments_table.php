<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_vietnamese_ci';

            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('enrollment_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('lesson_id');
            $table->text('content');
            $table->enum('status', ['visible', 'hidden'])->default('visible');
            $table->boolean('is_official')->default(false);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->index('parent_id', 'idx_comments_parent');
            $table->index('enrollment_id', 'idx_comments_enrollment');
            $table->index('user_id', 'idx_comments_user');
            $table->index(['lesson_id', 'status', 'created_at'], 'idx_comments_lesson_status');
            $table->foreign('enrollment_id', 'fk_comments_enrollment')->references('id')->on('enrollments')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('lesson_id', 'fk_comments_lesson')->references('id')->on('lessons')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('parent_id', 'fk_comments_parent')->references('id')->on('comments')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('user_id', 'fk_comments_user')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
        });

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_comments_reply_one_level_bi`
BEFORE INSERT ON `comments`
FOR EACH ROW
BEGIN
    IF NEW.parent_id IS NOT NULL
       AND EXISTS (
            SELECT 1
            FROM `comments`
            WHERE id = NEW.parent_id
              AND parent_id IS NOT NULL
       )
    THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Replies are limited to one level';
    END IF;
END
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_comments_reply_one_level_bu`
BEFORE UPDATE ON `comments`
FOR EACH ROW
BEGIN
    IF NEW.parent_id IS NOT NULL
       AND EXISTS (
            SELECT 1
            FROM `comments`
            WHERE id = NEW.parent_id
              AND parent_id IS NOT NULL
       )
    THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Replies are limited to one level';
    END IF;
END
SQL);

    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_comments_reply_one_level_bu`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_comments_reply_one_level_bi`');
        Schema::dropIfExists('comments');
    }
};