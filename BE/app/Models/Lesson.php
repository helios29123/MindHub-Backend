<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    public const TYPE_VIDEO = 'video';
    public const TYPE_TEXT = 'text';
    public const TYPE_DOCUMENT = 'document';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_HIDDEN = 'hidden';

    protected $fillable = [
        'course_section_id',
        'course_id',
        'title',
        'lesson_type',
        'content',
        'video_url',
        'video_id',
        'video_duration_seconds',
        'is_preview',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'course_section_id' => 'integer',
            'course_id' => 'integer',
            'video_duration_seconds' => 'integer',
            'is_preview' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'course_section_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(LessonAsset::class);
    }

    public function progressRecords(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function videoProgressRecords(): HasMany
    {
        return $this->hasMany(VideoProgress::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(LessonNote::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}