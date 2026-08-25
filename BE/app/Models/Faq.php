<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Faq extends Model
{
    public const SOURCE_SYSTEM = 'system';
    public const SOURCE_INSTRUCTOR = 'instructor';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'question',
        'answer',
        'type',
        'source',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_faqs', 'faq_id', 'course_id')
            ->withPivot('sort_order')
            ->orderBy('course_faqs.sort_order');
    }
}