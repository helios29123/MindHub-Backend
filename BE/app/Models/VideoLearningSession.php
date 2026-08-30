<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class VideoLearningSession extends Model {
    protected $fillable=['token_hash','enrollment_id','lesson_id','status','current_position','playback_rate','started_at','last_heartbeat_at','ended_at'];
    protected function casts(): array { return ['current_position'=>'integer','playback_rate'=>'decimal:2','started_at'=>'datetime','last_heartbeat_at'=>'datetime','ended_at'=>'datetime']; }
}
