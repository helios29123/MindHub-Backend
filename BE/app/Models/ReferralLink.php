<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ReferralLink extends Model
{
    protected $fillable = ['instructor_id', 'course_id', 'code', 'source', 'campaign_name', 'description', 'clicks', 'conversions', 'revenue_amount', 'is_active'];
    protected $casts = ['is_active' => 'boolean', 'revenue_amount' => 'decimal:2'];
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
