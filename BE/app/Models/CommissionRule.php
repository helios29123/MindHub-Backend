<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class CommissionRule extends Model
{
    protected $table = 'commission_rules';

    protected $guarded = [];

    protected $casts = [
        'platform_rate' => 'float',
        'instructor_rate' => 'float',
        'platform_rate_percent' => 'float',
        'instructor_rate_percent' => 'float',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}