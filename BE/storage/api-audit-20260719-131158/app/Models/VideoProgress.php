<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoProgress extends Model
{
    protected $table = 'video_progress';

    protected $fillable = [
        'lesson_id',
        'user_id',
        'current_second',
    ];

    protected $casts = [
        'current_second' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
