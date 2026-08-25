<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommissionRule extends Model
{
    protected $fillable = [
        'name',
        'description',
        'instructor_rate',
        'platform_rate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'instructor_rate' => 'decimal:4',
            'platform_rate' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function revenues(): HasMany
    {
        return $this->hasMany(Revenue::class);
    }
}