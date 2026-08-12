<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstructorCreditTransaction extends Model
{
    public const TYPE_PURCHASE = 'purchase';
    public const TYPE_USE = 'use';
    public const TYPE_REFUND = 'refund';
    public const TYPE_ADJUST = 'adjust';

    protected $fillable = [
        'instructor_id',
        'order_id',
        'course_id',
        'type',
        'credits',
        'balance_before',
        'balance_after',
        'note',
    ];

    protected $casts = [
        'instructor_id' => 'integer',
        'order_id' => 'integer',
        'course_id' => 'integer',
        'credits' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
    ];
}
