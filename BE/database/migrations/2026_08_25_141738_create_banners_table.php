<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_vietnamese_ci';

            $table->id();
            $table->string('title', 255);
            $table->string('image_url', 2048);
            $table->string('image_public_id', 255)->nullable()->unique('uq_banners_image_public_id');
            $table->string('target_url', 2048)->nullable();
            $table->string('position', 100);
            $table->unsignedInteger('sort_order')->default(0);
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->index(['position', 'status', 'sort_order'], 'idx_banners_position_status_order');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};