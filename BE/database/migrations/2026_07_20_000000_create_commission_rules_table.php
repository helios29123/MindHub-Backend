<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('commission_rules')) {
            Schema::create('commission_rules', function (Blueprint $table) {
                $table->id();
                $table->string('sale_channel', 100)->unique()->index();
                $table->string('name', 255)->nullable();
                $table->text('description')->nullable();
                $table->decimal('instructor_rate', 5, 2)->default(70.00);
                $table->decimal('platform_rate', 5, 2)->default(30.00);
                $table->decimal('instructor_rate_percent', 5, 2)->nullable();
                $table->decimal('platform_rate_percent', 5, 2)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_rules');
    }
};
