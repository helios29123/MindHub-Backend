<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonNote extends Model
{
    protected $fillable = [
        'enrollment_id',
        'lesson_id',
        'content',
        'note_time_second',
    ];

    protected function casts(): array
    {
        return [
            'enrollment_id' => 'integer',
            'lesson_id' => 'integer',
            'note_time_second' => 'integer',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}