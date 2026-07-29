<?php

namespace App\Services\Moderation;

use App\Exceptions\BusinessException;
use App\Models\Comment;
use App\Models\CourseReview;

class ModerationService
{
    public function moderateItem(int $id, array $data): mixed
    {
        $targetType = $data['target_type'];
        $status = $data['status'];

        if ($targetType === 'comment') {
            $comment = Comment::find($id);

            if (!$comment) {
                throw new BusinessException('Không tìm thấy dữ liệu.', 404);
            }

            $comment->status = $status;
            $comment->save();

            return $comment;
        }

        if ($targetType === 'review') {
            $review = CourseReview::withTrashed()->find($id);

            if (!$review) {
                throw new BusinessException('Không tìm thấy dữ liệu.', 404);
            }

            if ($status === 'deleted') {
                $review->delete();
            } else {
                $review->restore();
            }

            return $review;
        }

        throw new BusinessException('Loại dữ liệu kiểm duyệt không hợp lệ.', 422);
    }
}
