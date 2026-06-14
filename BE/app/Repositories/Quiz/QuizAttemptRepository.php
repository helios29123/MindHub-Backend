<?php

namespace App\Repositories\Quiz;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class QuizAttemptRepository
{
    public function getPassedQuizIds(int $userId, array $quizIds): Collection
    {
        if ($quizIds === []) {
            return collect();
        }

        return DB::table('quiz_attempts')
            ->where('user_id', $userId)
            ->whereIn('quiz_id', $quizIds)
            ->where('status', 'submitted')
            ->where('passed', true)
            ->distinct()
            ->pluck('quiz_id');
    }

    public function getLatestAttempts(int $userId, array $quizIds): array
    {
        if ($quizIds === []) {
            return [];
        }

        $attempts = DB::table('quiz_attempts')
            ->where('user_id', $userId)
            ->whereIn('quiz_id', $quizIds)
            ->orderBy('quiz_id')
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->get();

        $latestByQuiz = [];

        foreach ($attempts as $attempt) {
            $quizId = (int) $attempt->quiz_id;

            if (array_key_exists($quizId, $latestByQuiz)) {
                continue;
            }

            $latestByQuiz[$quizId] = [
                'attempt_id' => (int) $attempt->id,
                'quiz_id' => $quizId,
                'attempt_number' => (int) $attempt->attempt_number,
                'score' => number_format((float) $attempt->score, 2, '.', ''),
                'total_score' => number_format((float) $attempt->total_score, 2, '.', ''),
                'passed' => (bool) $attempt->passed,
                'status' => $attempt->status,
                'started_at' => $attempt->started_at,
                'submitted_at' => $attempt->submitted_at,
            ];
        }

        return array_values($latestByQuiz);
    }
}