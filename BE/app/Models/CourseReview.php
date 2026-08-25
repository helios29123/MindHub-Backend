<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseReview extends Model
{
    protected $fillable = [
        'order_id',
        'rating',
        'comment',
        'edited_at',
    ];

    protected function casts(): array
    {
        return [
            'order_id' => 'integer',
            'rating' => 'integer',
            'edited_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}