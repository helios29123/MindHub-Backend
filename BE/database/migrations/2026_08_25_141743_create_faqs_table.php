<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->string('question', 1000);
            $table->text('answer');
            $table->string('type', 100)->default('general');
            $table->enum('source', ['system', 'instructor'])->default('system');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->index(['type', 'status', 'sort_order'], 'idx_faqs_type_status_order');
            $table->index(['source', 'type', 'status'], 'idx_faqs_source_type_status');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};