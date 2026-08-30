<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoProgress extends Model
{
    protected $table = 'video_progress';

    protected $fillable = [
        'enrollment_id',
        'lesson_id',
        'current_second',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'lesson_id' => 'integer',
            'current_second' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}