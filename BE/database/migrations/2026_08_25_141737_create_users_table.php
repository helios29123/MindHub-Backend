<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_vietnamese_ci';

            $table->id();
            $table->string('full_name', 150);
            $table->string('email', 255)->unique('uq_users_email');
            $table->string('phone', 30)->nullable()->unique('uq_users_phone');
            $table->string('password_hash', 255);
            $table->string('avatar_url', 2048)->nullable();
            $table->string('avatar_public_id', 255)->nullable()->unique('uq_users_avatar_public_id');
            $table->enum('role', ['learner', 'instructor', 'admin'])->default('learner');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->boolean('locked')->default(false);
            $table->string('locked_reason', 500)->nullable();
            $table->dateTime('email_verified_at')->nullable();
            $table->dateTime('last_login_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->index(['role', 'status'], 'idx_users_role_status');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};