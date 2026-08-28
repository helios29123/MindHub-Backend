<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonProgress extends Model
{
    public const STATUS_NOT_STARTED = 'not_started';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';

    protected $table = 'lesson_progress';

    protected $fillable = [
        'user_id',
        'lesson_id',
        'status',
        'started_at',
        'completed_at',
        'learning_duration_seconds',
        'last_accessed_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'lesson_id' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'learning_duration_seconds' => 'integer',
            'last_accessed_at' => 'datetime',
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