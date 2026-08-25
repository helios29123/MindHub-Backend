<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstructorProfile extends Model
{
    public const RANK_BRONZE = 'bronze';
    public const RANK_SILVER = 'silver';
    public const RANK_GOLD = 'gold';
    public const RANK_DIAMOND = 'diamond';

    protected $fillable = [
        'user_id',
        'bio',
        'expertise',
        'experience_years',
        'instructor_rank',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'experience_years' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}