<?php
namespace App\Models;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\InstructorProfile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

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
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'locked' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
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
            ->whereNull('deleted_at');
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
            ->whereNull('courses.deleted_at')
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
