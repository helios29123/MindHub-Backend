<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttemptAnswer extends Model
{
    protected $table = 'quiz_attempt_answers';

    public $timestamps = false;

    protected $fillable = [
        'attempt_id',
        'question_id',
        'option_id',
        'is_correct',
        'score_earned',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'score_earned' => 'decimal:2',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class, 'attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(QuizOption::class, 'option_id');
    }
}