<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "instructor_id",
        "title",
        "slug",
        "short_description",
        "description",
        "thumbnail_url",
        "intro_video_url",
        "price",
        "sale_price",
        "level",
        "language",
        "requirements",
        "outcomes",
        "status",
        "is_featured",
        "total_duration_seconds",
        "published_at",
        "admin_reject_reason",
    ];

    protected $casts = [
        "price" => "decimal:2",
        "sale_price" => "decimal:2",
        "is_featured" => "boolean",
        "published_at" => "datetime",
    ];

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, "instructor_id");
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            "course_categories",
            "course_id",
            "category_id",
        )->withPivot("created_at");
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function activeEnrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class)->whereIn("status", [
            "active",
            "completed",
        ]);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(
            CourseReview::class,
            Order::class,
            "course_id",
            "order_id",
            "id",
            "id",
        );
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CourseSection::class)->orderBy("sort_order");
    }

    public function faqs(): BelongsToMany
    {
        return $this->belongsToMany(
            Faq::class,
            "course_faqs",
            "course_id",
            "faq_id",
        )
            ->withPivot("sort_order")
            ->orderBy("course_faqs.sort_order")
            ->whereNull("course_faqs.deleted_at");
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

}
