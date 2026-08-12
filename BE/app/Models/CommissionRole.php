<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class CommissionRule extends Model
{
    protected $fillable = ['sale_channel', 'instructor_rate', 'platform_rate', 'description', 'is_active'];
    protected $casts = ['instructor_rate' => 'decimal:2', 'platform_rate' => 'decimal:2', 'is_active' => 'boolean'];
}
