<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instructor_profiles', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->unsignedBigInteger('user_id')->unique('uq_instructor_profiles_user');
            $table->text('bio')->nullable();
            $table->string('expertise', 500)->nullable();
            $table->unsignedSmallInteger('experience_years')->default(0);
            $table->enum('instructor_rank', ['bronze', 'silver', 'gold', 'diamond'])->default('bronze');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->foreign('user_id', 'fk_instructor_profiles_user')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('instructor_profiles');
    }
};