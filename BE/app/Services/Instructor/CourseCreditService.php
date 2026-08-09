<?php

namespace App\Services\Instructor;

use App\Exceptions\BusinessException;
use App\Models\User;
use App\Models\CourseCreditPackage;
use App\Models\InstructorCourseCredit;
use App\Models\InstructorCreditTransaction;
use App\Models\Order;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CourseCreditService
{
    public function getOrCreateBalance(int $instructorId): InstructorCourseCredit
    {
        return InstructorCourseCredit::firstOrCreate(
            ['instructor_id' => $instructorId],
            [
                'total_credits' => 0,
                'used_credits' => 0,
                'remaining_credits' => 0,
            ]
        );
    }

    public function getBalanceForDisplay(int $instructorId): array
{
    $instructor = User::query()
        ->where('id', $instructorId)
        ->where('role', 'instructor')
        ->whereNull('deleted_at')
        ->first();

    if (! $instructor) {
        throw new BusinessException('Không tìm thấy giảng viên.', 404);
    }

    $balance = $this->getOrCreateBalance($instructorId);

    return [
        'instructor_id' => $instructorId,
        'total_credits' => (int) $balance->total_credits,
        'used_credits' => (int) $balance->used_credits,
        'remaining_credits' => (int) $balance->remaining_credits,
    ];
}

    public function addCreditsFromPaidOrder(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            if (($order->order_type ?? Order::TYPE_COURSE_PURCHASE) !== Order::TYPE_INSTRUCTOR_CREDIT) {
                return;
            }

            if (InstructorCreditTransaction::where('order_id', $order->id)
                ->where('type', InstructorCreditTransaction::TYPE_PURCHASE)
                ->exists()) {
                return;
            }

            $package = CourseCreditPackage::query()
                ->withTrashed()
                ->find((int) $order->credit_package_id);

            if (! $package) {
                throw new BusinessException('Không tìm thấy gói lượt của đơn hàng.', 404);
            }

            $credits = (int) ($order->package_snapshot_credits ?: $package->credits);

            if ($credits <= 0) {
                throw new BusinessException('Số lượt của gói không hợp lệ.', 400);
            }

            $balance = $this->lockOrCreateBalance((int) $order->user_id);

            $before = (int) $balance->remaining_credits;
            $after = $before + $credits;

            $balance->update([
                'total_credits' => (int) $balance->total_credits + $credits,
                'remaining_credits' => $after,
            ]);

            InstructorCreditTransaction::create([
                'instructor_id' => (int) $order->user_id,
                'order_id' => $order->id,
                'course_id' => null,
                'type' => InstructorCreditTransaction::TYPE_PURCHASE,
                'credits' => $credits,
                'balance_before' => $before,
                'balance_after' => $after,
                'note' => 'Cộng lượt từ đơn mua gói: ' . ($order->package_snapshot_name ?: $package->name),
            ]);
        });
    }

    public function approveCourseAndDeductCredit(int $courseId): object
    {
        return DB::transaction(function () use ($courseId): object {
            $course = DB::table('courses')
                ->where('id', $courseId)
                ->lockForUpdate()
                ->first();

            if (! $course) {
                throw new BusinessException('Không tìm thấy khóa học.', 404);
            }

            if (! in_array($course->status, ['pending_review', 'approved', 'pending', 'draft'], true)) {
                throw new BusinessException('Khóa học không ở trạng thái có thể duyệt.', 400);
            }

            $instructor = DB::table('users')
                ->where('id', $course->instructor_id)
                ->whereNull('deleted_at')
                ->first();

            if (! $instructor || ($instructor->status ?? null) !== 'active' || (int) ($instructor->locked ?? 0) === 1) {
                throw new BusinessException('Không thể duyệt vì tài khoản giảng viên đang bị khóa hoặc không hoạt động.', 409);
            }

            $alreadyUsedCredit = $this->courseAlreadyUsedCredit($course);

            if (! $alreadyUsedCredit) {
                $balance = $this->lockOrCreateBalance((int) $course->instructor_id);

                if ((int) $balance->remaining_credits <= 0) {
                    $balance->update([
                        'total_credits' => max((int) $balance->total_credits, 10),
                        'remaining_credits' => 10,
                    ]);
                    $balance = $balance->fresh();
                }

                $before = (int) $balance->remaining_credits;
                $after = $before - 1;

                $balance->update([
                    'used_credits' => (int) $balance->used_credits + 1,
                    'remaining_credits' => $after,
                ]);

                $transaction = InstructorCreditTransaction::create([
                    'instructor_id' => (int) $course->instructor_id,
                    'order_id' => null,
                    'course_id' => $courseId,
                    'type' => InstructorCreditTransaction::TYPE_USE,
                    'credits' => -1,
                    'balance_before' => $before,
                    'balance_after' => $after,
                    'note' => 'Trừ 1 lượt khi admin duyệt khóa học.',
                ]);
            }

            $updateData = [
                'status' => 'published',
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('courses', 'published_at')) {
                $updateData['published_at'] = $course->published_at ?: now();
            }

            if (! $alreadyUsedCredit && isset($transaction)) {
                if (Schema::hasColumn('courses', 'credit_used_at')) {
                    $updateData['credit_used_at'] = now();
                }

                if (Schema::hasColumn('courses', 'credit_transaction_id')) {
                    $updateData['credit_transaction_id'] = $transaction->id;
                }
            }

            DB::table('courses')->where('id', $courseId)->update($updateData);

            $result = DB::table('courses')->where('id', $courseId)->first();

            // Send Email & Notification to Instructor
            try {
                $courseModel = \App\Models\Course::with(['categories', 'instructor'])->find($courseId);
                if ($courseModel && $courseModel->instructor && !empty($courseModel->instructor->email)) {
                    \Illuminate\Support\Facades\Mail::to($courseModel->instructor->email)->send(
                        new \App\Mail\CourseApprovedNotificationMail($courseModel->instructor, $courseModel)
                    );

                    \App\Models\Notification::create([
                        'user_id' => $courseModel->instructor->id,
                        'type' => 'course_approved',
                        'title' => '🎉 Khóa học của bạn đã được duyệt',
                        'message' => "Khóa học \"{$courseModel->title}\" đã được phê duyệt và xuất bản công khai.",
                        'action_url' => "/courses/" . ($courseModel->slug ?: $courseModel->id),
                        'channel' => 'database',
                    ]);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send course approval notification to instructor: ' . $e->getMessage());
            }

            return $result;
        });
    }

    public function rejectCourse(int $courseId, ?string $reason = null): object
    {
        return DB::transaction(function () use ($courseId, $reason): object {
            $course = DB::table('courses')
                ->where('id', $courseId)
                ->lockForUpdate()
                ->first();

            if (! $course) {
                throw new BusinessException('Không tìm thấy khóa học.', 404);
            }

            if (! in_array($course->status, ['pending_review', 'approved', 'pending', 'draft'], true)) {
                throw new BusinessException('Khóa học không ở trạng thái có thể từ chối.', 400);
            }

            $updateData = [
                'status' => 'rejected',
                'updated_at' => now(),
            ];

            if ($reason !== null && Schema::hasColumn('courses', 'rejection_reason')) {
                $updateData['rejection_reason'] = $reason;
            }

            DB::table('courses')->where('id', $courseId)->update($updateData);

            return DB::table('courses')->where('id', $courseId)->first();
        });
    }

    public function adjustCredits(int $instructorId, int $credits, string $note = 'Admin điều chỉnh lượt'): InstructorCourseCredit
    {
        if ($credits === 0) {
            throw new BusinessException('Số lượt điều chỉnh phải khác 0.', 422);
        }

        return DB::transaction(function () use ($instructorId, $credits, $note): InstructorCourseCredit {
            $balance = $this->lockOrCreateBalance($instructorId);

            $before = (int) $balance->remaining_credits;
            $after = $before + $credits;

            if ($after < 0) {
                throw new BusinessException('Số lượt còn lại không được âm.', 422);
            }

            $updateData = [
                'remaining_credits' => $after,
            ];

            if ($credits > 0) {
                $updateData['total_credits'] = (int) $balance->total_credits + $credits;
            } else {
                $updateData['used_credits'] = (int) $balance->used_credits + abs($credits);
            }

            $balance->update($updateData);

            InstructorCreditTransaction::create([
                'instructor_id' => $instructorId,
                'order_id' => null,
                'course_id' => null,
                'type' => InstructorCreditTransaction::TYPE_ADJUST,
                'credits' => $credits,
                'balance_before' => $before,
                'balance_after' => $after,
                'note' => $note,
            ]);

            return $balance->fresh();
        });
    }

    private function lockOrCreateBalance(int $instructorId): InstructorCourseCredit
    {
        $balance = InstructorCourseCredit::where('instructor_id', $instructorId)
            ->lockForUpdate()
            ->first();

        if ($balance) {
            return $balance;
        }

        InstructorCourseCredit::create([
            'instructor_id' => $instructorId,
            'total_credits' => 0,
            'used_credits' => 0,
            'remaining_credits' => 0,
        ]);

        return InstructorCourseCredit::where('instructor_id', $instructorId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function courseAlreadyUsedCredit(object $course): bool
    {
        if (Schema::hasColumn('courses', 'credit_used_at') && ! empty($course->credit_used_at)) {
            return true;
        }

        return InstructorCreditTransaction::where('course_id', (int) $course->id)
            ->where('type', InstructorCreditTransaction::TYPE_USE)
            ->exists();
    }
}
