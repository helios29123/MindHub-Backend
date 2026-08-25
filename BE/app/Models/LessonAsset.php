<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonAsset extends Model
{
    protected $fillable = [
        'lesson_id',
        'title',
        'file_url',
        'file_id',
        'file_name',
        'file_type',
        'file_size',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'lesson_id' => 'integer',
            'file_size' => 'integer',
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}