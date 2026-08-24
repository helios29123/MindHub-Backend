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
        } else {
            Schema::table('user_otps', function (Blueprint $table) {
                if (!Schema::hasColumn('user_otps', 'purpose')) {
                    $table->string('purpose', 50)->nullable()->after('user_id');
                }
                if (!Schema::hasColumn('user_otps', 'code_hash')) {
                    $table->string('code_hash', 255)->nullable()->after('purpose');
                }
                if (!Schema::hasColumn('user_otps', 'attempts')) {
                    $table->integer('attempts')->default(0)->after('used_at');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_otps');
    }
};
