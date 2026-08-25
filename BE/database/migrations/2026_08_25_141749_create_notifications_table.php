<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type', 100);
            $table->string('title', 255);
            $table->text('message');
            $table->json('data')->nullable();
            $table->string('action_url', 2048)->nullable();
            $table->enum('channel', ['web', 'email', 'both'])->default('web');
            $table->dateTime('read_at')->nullable();
            $table->enum('email_status', ['pending', 'sent', 'failed', 'skipped'])->nullable();
            $table->dateTime('email_sent_at')->nullable();
            $table->text('email_error')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->index(['user_id', 'read_at', 'created_at'], 'idx_notifications_user_read');
            $table->index('email_status', 'idx_notifications_email_status');
            $table->foreign('user_id', 'fk_notifications_user')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};