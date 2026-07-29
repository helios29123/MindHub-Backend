<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LessonAsset extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lesson_assets';

    public const UPDATED_AT = null;

    protected $fillable = [
        'lesson_id',
        'title',
        'file_url',
        'file_name',
        'file_type',
        'file_size',
        'note',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}