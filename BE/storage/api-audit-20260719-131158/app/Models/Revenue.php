<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Revenue extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_WITHDRAWN = 'withdrawn';
    public const STATUS_CANCELLED = 'cancelled';

    public $timestamps = false;

    protected $fillable = [
        'instructor_id',
        'course_id',
        'order_id',
        'gross_amount',
        'instructor_amount',
        'platform_fee_amount',
        'status',
        'earned_at',
        'created_at',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'instructor_amount' => 'decimal:2',
        'platform_fee_amount' => 'decimal:2',
        'earned_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
