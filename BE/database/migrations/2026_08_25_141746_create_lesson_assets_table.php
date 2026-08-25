<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_assets', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->unsignedBigInteger('lesson_id');
            $table->string('title', 255);
            $table->string('file_url', 2048);
            $table->string('file_id', 255)->nullable()->unique('uq_lesson_assets_file_id');
            $table->string('file_name', 255);
            $table->string('file_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('note', 1000)->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->index('lesson_id', 'idx_lesson_assets_lesson');
            $table->foreign('lesson_id', 'fk_lesson_assets_lesson')->references('id')->on('lessons')->cascadeOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_assets');
    }
};