<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchClick extends Model
{
    use HasFactory;

    protected $table = 'search_clicks';

    protected $fillable = [
        'search_log_id',
        'course_id',
        'query',
        'clicked_position',
        'time_to_click_seconds',
    ];

    protected $casts = [
        'clicked_position' => 'integer',
        'time_to_click_seconds' => 'integer',
    ];

    public function searchLog(): BelongsTo
    {
        return $this->belongsTo(SearchLog::class, 'search_log_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
