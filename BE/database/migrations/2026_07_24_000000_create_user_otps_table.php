<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_otps')) {
            Schema::create('user_otps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('purpose', 50);
                $table->string('code_hash', 255);
                $table->timestamp('expires_at');
                $table->timestamp('used_at')->nullable();
                $table->integer('attempts')->default(0);
                $table->timestamps();

                $table->index(['user_id', 'purpose', 'expires_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_otps');
    }
};
