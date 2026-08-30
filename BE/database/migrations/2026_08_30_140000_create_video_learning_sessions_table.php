<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('video_learning_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('token_hash',64)->unique();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('status',['active','ended','expired','replaced'])->default('active');
            $table->unsignedInteger('current_position')->default(0);
            $table->decimal('playback_rate',4,2)->default(1.00);
            $table->timestamp('started_at');
            $table->timestamp('last_heartbeat_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->index(['enrollment_id','lesson_id','status'],'idx_video_session_enrollment_lesson_status');
        });
    }
    public function down(): void { Schema::dropIfExists('video_learning_sessions'); }
};
