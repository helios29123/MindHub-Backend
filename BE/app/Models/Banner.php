<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'title',
        'image_url',
        'image_public_id',
        'target_url',
        'position',
        'sort_order',
        'start_at',
        'end_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
        ];
    }
}