<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseEmbedding extends Model
{
    use HasFactory;

    protected $table = 'course_embeddings';

    protected $fillable = [
        'course_id',
        'embedding_model',
        'dimensions',
        'vector',
        'payload_hash',
        'content_summary',
    ];

    protected $casts = [
        'dimensions' => 'integer',
        'vector' => 'array',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
