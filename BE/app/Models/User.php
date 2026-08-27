<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_LEARNER = 'learner';
    public const ROLE_INSTRUCTOR = 'instructor';
    public const ROLE_ADMIN = 'admin';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';

    protected $table = 'users';

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'password_hash',
        'avatar_url',
        'avatar_public_id',
        'role',
        'status',
        'locked',
        'locked_reason',
        'email_verified_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected function casts(): array
    {
        return [
            'locked' => 'boolean',
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function getAuthPassword(): string
    {
        return (string) $this->password_hash;
    }

    public function getRememberTokenName(): ?string
    {
        return null;
    }

    /**
     * Tài khoản có đang bị admin khóa thủ công hay không.
     *
     * DB FINAL dùng cột users.locked riêng; status không có giá trị "locked".
     */
    public function isLocked(): bool
    {
        return (bool) $this->locked;
    }

    /**
     * Trạng thái tài khoản có đang active hay không.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function instructorProfile(): HasOne
    {
        return $this->hasOne(InstructorProfile::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    public function reviewedCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'reviewed_by');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function payoutAccounts(): HasMany
    {
        return $this->hasMany(PayoutAccount::class);
    }

    public function withdrawRequests(): HasMany
    {
        return $this->hasMany(WithdrawRequest::class);
    }

    public function revenues(): HasMany
    {
        return $this->hasMany(Revenue::class, 'instructor_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    public function otps(): HasMany
    {
        return $this->hasMany(UserOtp::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function wishlistCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'wishlist', 'user_id', 'course_id')
            ->withPivot('created_at');
    }
}