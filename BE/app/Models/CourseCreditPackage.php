<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseCreditPackage extends Model
{

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $table = 'course_credit_packages';

    protected $fillable = [
        'name',
        'description',
        'credits',
        'price',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'credits' => 'integer',
        'price' => 'decimal:2',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',    ];
}