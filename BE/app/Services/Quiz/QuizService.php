<?php

namespace App\Services\Quiz;

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
        // 1. Tﾃｬm quiz status=published vﾃ course/lesson liﾃｪn quan.
        $quiz = Quiz::with(['course', 'lesson'])->find($quizId);

        if (!$quiz) {
            throw new BusinessException('Khﾃｴng tﾃｬm th蘯･y d盻ｯ li盻㎡.', 404);
        }

        // Check quiz status
        if ($quiz->status !== 'published') {
            throw new BusinessException('N盻冓 dung chﾆｰa kh蘯｣ d盻･ng.', 403);
        }

        // Check course status
        $course = $quiz->course;
        if (!$course || $course->status !== 'published') {
            throw new BusinessException('N盻冓 dung chﾆｰa kh蘯｣ d盻･ng.', 403);
        }

        // Check lesson status if linked to a lesson
        if ($quiz->lesson_id) {
            $lesson = $quiz->lesson;
            if (!$lesson || $lesson->status !== 'published') {
                throw new BusinessException('N盻冓 dung chﾆｰa kh蘯｣ d盻･ng.', 403);
            }
        }

        // 2. Ki盻ノ tra learner cﾃｳ enrollment active/completed trong quiz.course_id.
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $quiz->course_id)
            ->whereIn('status', ['active', 'completed'])
            ->first();

        if (!$enrollment) {
            throw new BusinessException('B蘯｡n chﾆｰa cﾃｳ quy盻］ truy c蘯ｭp n盻冓 dung nﾃy.', 403);
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
                throw new BusinessException('ﾄ静｡p ﾃ｡n khﾃｴng h盻｣p l盻・cho cﾃ｢u h盻淑.', 422);
            }
            $answeredQuestionIds[] = $qId;

            $question = $questionsMap->get($qId);
            if (!$question) {
                throw new BusinessException('ﾄ静｡p ﾃ｡n khﾃｴng h盻｣p l盻・cho cﾃ｢u h盻淑.', 422);
            }

            $option = $question->options->firstWhere('id', $optId);
            if (!$option) {
                throw new BusinessException('ﾄ静｡p ﾃ｡n khﾃｴng h盻｣p l盻・cho cﾃ｢u h盻淑.', 422);
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
                    throw new BusinessException('B蘯｡n ﾄ妥｣ n盻冪 attempt nﾃy.', 409);
                }
                throw $e;
            }
        });
    }

    public function completionStatus(int $courseId, array $filters, User $user): array
    {
        $quizRepository = app(\App\Repositories\Quiz\QuizRepository::class);
        $quizAttemptRepository = app(\App\Repositories\Quiz\QuizAttemptRepository::class);

        $course = $quizRepository->findCourseById($courseId);

        if (!$course) {
            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        if ($course->status !== 'published') {
            throw new BusinessException('Nội dung chưa khả dụng.', 403);
        }

        $enrollment = $quizRepository->findEnrollment((int) $user->id, (int) $course->id);

        if (!$enrollment) {
            throw new BusinessException('Bạn chưa có quyền truy cập nội dung này.', 403);
        }

        $publishedLessonCount = $quizRepository->countPublishedLessons((int) $course->id);
        $progressPercent = (float) $enrollment->progress_percent;

        /*
         * ERD GD1 không có bảng lesson_progress/learning_histories.
         * Vì vậy điều kiện "lessons completed" được xác định bằng enrollments.progress_percent.
         */
        $lessonsCompleted = $publishedLessonCount === 0 || $progressPercent >= 100.0;

        $publishedQuizIds = $quizRepository
            ->getPublishedQuizIds((int) $course->id)
            ->map(fn ($id): int => (int) $id)
            ->values();

        $passedQuizIds = $quizAttemptRepository
            ->getPassedQuizIds((int) $user->id, $publishedQuizIds->all())
            ->map(fn ($id): int => (int) $id)
            ->values();

        $totalPublishedQuizzes = $publishedQuizIds->count();
        $passedQuizCount = $passedQuizIds->count();
        $remainingQuizIds = $publishedQuizIds
            ->diff($passedQuizIds)
            ->values()
            ->all();

        $allQuizzesPassed = $totalPublishedQuizzes === 0 || $passedQuizCount === $totalPublishedQuizzes;
        $isCompleted = $lessonsCompleted && $allQuizzesPassed;

        if ($isCompleted && $enrollment->status !== 'completed') {
            DB::transaction(function () use ($enrollment): void {
                $enrollment->update([
                    'status' => 'completed',
                    'progress_percent' => 100,
                    'completed_at' => now(),
                    'last_accessed_at' => now(),
                ]);
            });

            $enrollment->refresh();
        }

        return [
            'course' => [
                'id' => (int) $course->id,
                'title' => $course->title,
                'status' => $course->status,
            ],
            'enrollment' => [
                'id' => (int) $enrollment->id,
                'status' => $enrollment->status,
                'progress_percent' => number_format((float) $enrollment->progress_percent, 2, '.', ''),
                'enrolled_at' => optional($enrollment->enrolled_at)->toDateTimeString(),
                'completed_at' => optional($enrollment->completed_at)->toDateTimeString(),
                'last_accessed_at' => optional($enrollment->last_accessed_at)->toDateTimeString(),
            ],
            'lessons' => [
                'total_published_lessons' => $publishedLessonCount,
                'progress_percent' => number_format($progressPercent, 2, '.', ''),
                'completed' => $lessonsCompleted,
                'completion_source' => 'enrollments.progress_percent',
            ],
            'quizzes' => [
                'total_published_quizzes' => $totalPublishedQuizzes,
                'passed_quizzes' => $passedQuizCount,
                'remaining_quiz_ids' => $remainingQuizIds,
                'all_passed' => $allQuizzesPassed,
                'latest_attempts' => $quizAttemptRepository->getLatestAttempts(
                    (int) $user->id,
                    $publishedQuizIds->all(),
                ),
            ],
            'completion_status' => [
                'is_completed' => $isCompleted,
                'status' => $isCompleted ? 'completed' : 'in_progress',
                'completed_now' => $isCompleted && optional($enrollment->completed_at)->isToday(),
            ],
        ];
    }

    public function showAttemptResult(int $attemptId, int $userId): QuizAttempt
    {
        $attempt = QuizAttempt::with([
            'quiz.course',
            'quiz.lesson',
            'answers.question.options'
        ])->find($attemptId);

        if (!$attempt) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Không tìm thấy dữ liệu.');
        }

        if ($attempt->user_id !== $userId) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Bạn không được xem kết quả của người khác.');
        }

        $quiz = $attempt->quiz;
        
        if (!$quiz) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Không tìm thấy dữ liệu.');
        }

        if ($quiz->status !== 'published') {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Nội dung chưa khả dụng.');
        }

        $course = $quiz->course;
        if (!$course || $course->status !== 'published') {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Nội dung chưa khả dụng.');
        }

        if ($quiz->lesson_id) {
            $lesson = $quiz->lesson;
            if (!$lesson || $lesson->status !== 'published') {
                throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Nội dung chưa khả dụng.');
            }
        }

        $enrollment = Enrollment::where('user_id', $userId)
            ->where('course_id', $course->id)
            ->whereIn('status', ['active', 'completed'])
            ->first();

        if (!$enrollment) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Bạn chưa có quyền truy cập nội dung này.');
        }

        return $attempt;
    }
}
