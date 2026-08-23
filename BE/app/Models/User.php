<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_INSTRUCTOR = 'instructor';
    public const ROLE_LEARNER = 'learner';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_LOCKED = 'locked';

    protected $table = 'users';

    protected $fillable = [
        'full_name',
        'email',
        'password_hash',
        'phone',
        'avatar_url',
        'oauth_account_login',
        'role',
        'status',
        'email_verified_at',
        'last_login_at',
        'locked',
        'locked_reason',
        'password_reset',
    ];

    protected $hidden = [
        'password_hash',
        'password_reset',
    ];

    public function getRememberTokenName()
    {
        return null;
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'locked' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',        ];
    }

    public function getAvatarUrlAttribute(?string $value = null): ?string
    {
        $val = $value ?? ($this->attributes['avatar_url'] ?? null);
        if (empty($val)) {
            return null;
        }

        if (str_starts_with($val, 'http://') || str_starts_with($val, 'https://')) {
            return $val;
        }

        $path = ltrim($val, '/');
        if (str_starts_with($path, 'storage/')) {
            return url($path);
        }

        return url('storage/' . $path);
    }

    public function getNameAttribute(): ?string
    {
        return $this->attributes['full_name'] ?? null;
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['full_name'] = $value;
    }

    public function getPasswordAttribute(): ?string
    {
        return $this->attributes['password_hash'] ?? $this->attributes['password'] ?? null;
    }

    public function setPasswordAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['password_hash'] = null;
            return;
        }

        $this->attributes['password_hash'] = \Illuminate\Support\Facades\Hash::needsRehash((string) $value)
            ? \Illuminate\Support\Facades\Hash::make($value)
            : $value;
    }

    public function getAuthPassword(): string
    {
        return (string) $this->password_hash;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isInactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    public function isLocked(): bool
    {
        return $this->status === self::STATUS_LOCKED || $this->locked === true;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isInstructor(): bool
    {
        return $this->role === self::ROLE_INSTRUCTOR;
    }

    public function isLearner(): bool
    {
        return $this->role === self::ROLE_LEARNER;
    }

    public function instructorProfile(): HasOne
    {
        return $this->hasOne(InstructorProfile::class, 'user_id');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    public function publishedCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'instructor_id')
            ->where('status', 'published')
            ;
    }

    public function courseEnrollments(): HasManyThrough
    {
        return $this->hasManyThrough(
            Enrollment::class,
            Course::class,
            'instructor_id',
            'course_id',
            'id',
            'id'
        )
            ->where('courses.status', 'published')
            
            ->whereIn('enrollments.status', ['active', 'completed']);
    }

    public function hasVerifiedEmail(): bool
    {
        return ! is_null($this->email_verified_at);
    }

    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => now(),
        ])->save();
    }
}