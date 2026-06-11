<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use App\Models\QuizQuestion;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class QuizService
{
    public function storeAttempt(int $quizId, array $data, User $user): QuizAttempt
    {
        // 1. Tìm quiz status=published và course/lesson liên quan.
        $quiz = Quiz::with(['course', 'lesson'])->find($quizId);

        if (!$quiz) {
            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        // Check quiz status
        if ($quiz->status !== 'published') {
            throw new BusinessException('Nội dung chưa khả dụng.', 403);
        }

        // Check course status
        $course = $quiz->course;
        if (!$course || $course->status !== 'published') {
            throw new BusinessException('Nội dung chưa khả dụng.', 403);
        }

        // Check lesson status if linked to a lesson
        if ($quiz->lesson_id) {
            $lesson = $quiz->lesson;
            if (!$lesson || $lesson->status !== 'published') {
                throw new BusinessException('Nội dung chưa khả dụng.', 403);
            }
        }

        // 2. Kiểm tra learner có enrollment active/completed trong quiz.course_id.
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $quiz->course_id)
            ->whereIn('status', ['active', 'completed'])
            ->first();

        if (!$enrollment) {
            throw new BusinessException('Bạn chưa có quyền truy cập nội dung này.', 403);
        }

        // 3. Validate options and questions:
        // answers: array of { question_id, option_id }
        $answers = $data['answers'];
        
        $questions = QuizQuestion::where('quiz_id', $quiz->id)->with('options')->get();
        $questionsMap = $questions->keyBy('id');

        $answeredQuestionIds = [];
        foreach ($answers as $ans) {
            $qId = $ans['question_id'];
            $optId = $ans['option_id'];

            if (in_array($qId, $answeredQuestionIds)) {
                throw new BusinessException('Đáp án không hợp lệ cho câu hỏi.', 422);
            }
            $answeredQuestionIds[] = $qId;

            $question = $questionsMap->get($qId);
            if (!$question) {
                throw new BusinessException('Đáp án không hợp lệ cho câu hỏi.', 422);
            }

            $option = $question->options->firstWhere('id', $optId);
            if (!$option) {
                throw new BusinessException('Đáp án không hợp lệ cho câu hỏi.', 422);
            }
        }

        // 4. Create attempt inside a database transaction
        return DB::transaction(function () use ($quiz, $user, $answers, $questionsMap) {
            $maxAttemptNumber = QuizAttempt::where('quiz_id', $quiz->id)
                ->where('user_id', $user->id)
                ->max('attempt_number');

            $attemptNumber = ($maxAttemptNumber ?: 0) + 1;

            $totalScore = (float) $questionsMap->sum('score');
            $scoreEarned = 0.0;

            $answersToInsert = [];
            foreach ($answers as $ans) {
                $qId = $ans['question_id'];
                $optId = $ans['option_id'];

                $question = $questionsMap->get($qId);
                $option = $question->options->firstWhere('id', $optId);

                $isCorrect = (bool) $option->is_correct;
                $questionScore = (float) $question->score;
                $earned = $isCorrect ? $questionScore : 0.0;

                if ($isCorrect) {
                    $scoreEarned += $questionScore;
                }

                $answersToInsert[] = [
                    'question_id' => $qId,
                    'option_id' => $optId,
                    'is_correct' => $isCorrect,
                    'score_earned' => $earned,
                ];
            }

            $passed = $scoreEarned >= (float) $quiz->passing_score;

            try {
                $attempt = QuizAttempt::create([
                    'quiz_id' => $quiz->id,
                    'user_id' => $user->id,
                    'attempt_number' => $attemptNumber,
                    'score' => $scoreEarned,
                    'total_score' => $totalScore,
                    'passed' => $passed,
                    'status' => 'submitted',
                    'started_at' => now(),
                    'submitted_at' => now(),
                ]);

                foreach ($answersToInsert as $ansData) {
                    $ansData['attempt_id'] = $attempt->id;
                    QuizAnswer::create($ansData);
                }

                return $attempt;
            } catch (QueryException $e) {
                if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry')) {
                    throw new BusinessException('Bạn đã nộp attempt này.', 409);
                }
                throw $e;
            }
        });
    }
}
