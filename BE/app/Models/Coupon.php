<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    public const CAMPAIGN_DISCOUNT = 'discount';
    public const CAMPAIGN_TRIAL = 'trial';

    public const TYPE_PERCENT = 'percent';
    public const TYPE_FIXED = 'fixed';

    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_USED_UP = 'used_up';

    public const TERMINAL_STATUSES = [
        self::STATUS_INACTIVE,
        self::STATUS_EXPIRED,
        self::STATUS_USED_UP,
    ];

    protected $fillable = [
        'code',
        'course_id',
        'campaign_type',
        'discount_type',
        'discount_value',
        'max_discount_amount',
        'usage_limit',
        'used_count',
        'start_at',
        'end_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'course_id' => 'integer',
            'discount_value' => 'decimal:2',
            'max_discount_amount' => 'decimal:2',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, self::TERMINAL_STATUSES, true);
    }

    public function isTrial(): bool
    {
        return $this->campaign_type === self::CAMPAIGN_TRIAL;
    }

    public function isDiscount(): bool
    {
        return $this->campaign_type === self::CAMPAIGN_DISCOUNT;
    }

    public function isActiveNow(?CarbonInterface $at = null): bool
    {
        $at ??= now();

        if ($this->status === self::STATUS_INACTIVE) {
            return false;
        }
        if ($this->start_at !== null && $this->start_at->gt($at)) {
            return false;
        }
        if ($this->end_at !== null && $this->end_at->lt($at)) {
            return false;
        }
        if ($this->usage_limit !== null && (int) $this->used_count >= (int) $this->usage_limit) {
            return false;
        }

        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_SCHEDULED], true);
    }
}
