<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SearchLog extends Model
{
    use HasFactory;

    protected $table = 'search_logs';

    protected $fillable = [
        'user_id',
        'session_id',
        'query',
        'normalized_query',
        'search_type',
        'results_count',
        'ip_address',
    ];

    protected $casts = [
        'results_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(SearchClick::class, 'search_log_id');
    }
}
