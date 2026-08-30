<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LearningDailyActivity extends Model {
    protected $table='learning_daily_activity';
    protected $fillable=['enrollment_id','activity_date','video_learning_seconds'];
    protected function casts(): array { return ['activity_date'=>'date','video_learning_seconds'=>'integer']; }
}
