<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
class Lesson extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'lessons';
    protected $fillable = [
        'course_section_id',
        'course_id',
        'title',
        'slug',
        'lesson_type',
        'content',
        'video_url',
        'video_duration_seconds',
        'is_preview',
        'status',
        'sort_order',
    ];
    protected $casts = [
        'is_preview' => 'boolean',
        'video_duration_seconds' => 'integer',
        'sort_order' => 'integer',
    ];
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
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->where('status', 'visible');
    }
}
