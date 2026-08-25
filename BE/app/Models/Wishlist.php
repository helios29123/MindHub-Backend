<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wishlist extends Model
{
    protected $table = 'wishlist';

    public $incrementing = false;

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'course_id',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'course_id' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    /**
     * SQL FINAL dùng composite PK: (user_id, course_id).
     * Eloquent không hỗ trợ composite PK native.
     *
     * Không dùng Wishlist::find($id), whereKey(), hoặc route model binding
     * theo primary key mặc định.
     *
     * Ưu tiên:
     * - belongsToMany()->attach()/detach()/sync()
     * - hoặc query bằng cả user_id + course_id.
     */
    protected function setKeysForSelectQuery($query)
    {
        return $query
            ->where('user_id', '=', $this->getAttribute('user_id'))
            ->where('course_id', '=', $this->getAttribute('course_id'));
    }

    protected function setKeysForSaveQuery($query)
    {
        return $query
            ->where(
                'user_id',
                '=',
                $this->getRawOriginal('user_id') ?? $this->getAttribute('user_id')
            )
            ->where(
                'course_id',
                '=',
                $this->getRawOriginal('course_id') ?? $this->getAttribute('course_id')
            );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}