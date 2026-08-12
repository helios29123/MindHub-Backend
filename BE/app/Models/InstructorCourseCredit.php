<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstructorCourseCredit extends Model
{
    protected $fillable = [
        'instructor_id',
        'total_credits',
        'used_credits',
        'remaining_credits',
    ];

    protected $casts = [
        'instructor_id' => 'integer',
        'total_credits' => 'integer',
        'used_credits' => 'integer',
        'remaining_credits' => 'integer',
    ];
}
