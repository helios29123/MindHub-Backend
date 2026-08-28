<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('course_embeddings')) {
            Schema::create('course_embeddings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_id')->unique()->constrained('courses')->onDelete('cascade');
                $table->string('embedding_model', 64)->default('text-embedding-004');
                $table->unsignedInteger('dimensions')->default(768);
                $table->longText('vector')->comment('JSON Array of float numbers');
                $table->string('payload_hash', 64)->comment('MD5 hash of the vectorized content payload');
                $table->text('content_summary')->nullable()->comment('Brief summary of vectorized content');
                $table->timestamps();

                $table->index(['embedding_model', 'dimensions']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_embeddings');
    }
};
