<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('learning_daily_activity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnUpdate()->cascadeOnDelete();
            $table->date('activity_date');
            $table->unsignedInteger('video_learning_seconds')->default(0);
            $table->timestamps();
            $table->unique(['enrollment_id','activity_date'],'uq_learning_daily_enrollment_date');
        });
    }
    public function down(): void { Schema::dropIfExists('learning_daily_activity'); }
};
