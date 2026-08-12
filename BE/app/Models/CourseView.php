<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseView extends Model
{
    use HasFactory;

    protected $table = 'course_views';

    protected $fillable = [
        'course_id',
        'user_id',
        'session_id',
        'ip_hash',
        'user_agent_hash',
        'viewed_at',
    ];

    protected $casts = [
        'course_id' => 'integer',
        'user_id' => 'integer',
        'viewed_at' => 'datetime',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
