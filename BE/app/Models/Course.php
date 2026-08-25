<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Course extends Model
{
    use HasFactory;

    public const LEVEL_BEGINNER = 'beginner';
    public const LEVEL_INTERMEDIATE = 'intermediate';
    public const LEVEL_ADVANCED = 'advanced';
    public const LEVEL_ALL = 'all_levels';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING_REVIEW = 'pending_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_HIDDEN = 'hidden';

    protected $fillable = [
        'instructor_id',
        'title',
        'slug',
        'short_description',
        'description',
        'thumbnail_url',
        'thumbnail_public_id',
        'intro_video_url',
        'intro_video_id',
        'price',
        'discount_percent',
        'course_level',
        'language',
        'requirements',
        'outcomes',
        'status',
        'is_featured',
        'published_at',
        'reviewed_by',
        'admin_reject_reason',
    ];

    protected function casts(): array
    {
        return [
            'instructor_id' => 'integer',
            'price' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'requirements' => 'array',
            'outcomes' => 'array',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
            'reviewed_by' => 'integer',
        ];
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'course_categories', 'course_id', 'category_id');
    }

    public function coupon(): HasOne
    {
        return $this->hasOne(Coupon::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CourseSection::class)->orderBy('sort_order');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function revenues(): HasMany
    {
        return $this->hasMany(Revenue::class);
    }

    public function faqs(): BelongsToMany
    {
        return $this->belongsToMany(Faq::class, 'course_faqs', 'course_id', 'faq_id')
            ->withPivot('sort_order')
            ->orderBy('course_faqs.sort_order');
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function wishlistedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'wishlist', 'course_id', 'user_id')
            ->withPivot('created_at');
    }

    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(
            CourseReview::class,
            Order::class,
            'course_id',
            'order_id',
            'id',
            'id'
        );
    }
}