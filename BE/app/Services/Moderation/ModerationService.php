<?php

namespace App\Services\Moderation;

use App\Exceptions\BusinessException;
use App\Models\Comment;
use App\Models\CourseReview;
use App\Models\User;
use App\Models\Order;
use Carbon\Carbon;

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

    public function getModerationItems(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $perPage = max(1, (int) ($params['per_page'] ?? 20));
        $search = trim(mb_strtolower($params['search'] ?? '', 'UTF-8'));
        $targetType = $params['target_type'] ?? 'all';
        $status = $params['status'] ?? 'all';
        $userId = isset($params['user_id']) && $params['user_id'] !== '' ? (int) $params['user_id'] : null;
        $courseId = isset($params['course_id']) && $params['course_id'] !== '' ? (int) $params['course_id'] : null;
        $timePreset = $params['time_preset'] ?? 'all';
        $dateFrom = $params['date_from'] ?? '';
        $dateTo = $params['date_to'] ?? '';
        $sortBy = $params['sort_by'] ?? 'created_at';
        $sortDirection = $params['sort_direction'] ?? 'desc';
        $replyStatus = $params['reply_status'] ?? $params['priority_filter'] ?? 'all';
        $rating = $params['rating'] ?? 'all';

        // 1. Fetch comments and reviews with their relations
        $comments = Comment::with(['user', 'lesson.course', 'order.course', 'parent'])->get();
        $reviews = CourseReview::withTrashed()->with(['order.user', 'order.course'])->get();

        // 2. Helper warning evaluator leveraging ContentModeratorService
        $moderator = app(\App\Services\Moderation\ContentModeratorService::class);
        $evaluateWarningType = function ($content) use ($moderator) {
            if (!$content) return null;
            $modRes = $moderator->inspect($content);
            if ($modRes['is_violating']) {
                if (in_array('toxicity_and_profanity', $modRes['categories'])) return 'offensive';
                if (in_array('scam_and_spam', $modRes['categories']) || in_array('spam_link', $modRes['categories'])) return 'spam';
                return 'offensive';
            }
            return null;
        };

        // Base anchor time matching frontend mock date
        $now = Carbon::parse('2026-07-22T23:55:00.000Z');

        // Map comments
        $commentsList = $comments->map(function ($c) use ($comments, $evaluateWarningType, $now) {
            $courseIdVal = null;
            if ($c->lesson && $c->lesson->course_id) {
                $courseIdVal = $c->lesson->course_id;
            } elseif ($c->order && $c->order->course_id) {
                $courseIdVal = $c->order->course_id;
            } elseif ($c->parent && $c->parent->lesson && $c->parent->lesson->course_id) {
                $courseIdVal = $c->parent->lesson->course_id;
            }

            // Find course object
            $courseObj = null;
            if ($c->lesson && $c->lesson->course) {
                $courseObj = $c->lesson->course;
            } elseif ($c->order && $c->order->course) {
                $courseObj = $c->order->course;
            }

            // Find child replies
            $replies = $comments->filter(fn($reply) => $reply->parent_id && (int)$reply->parent_id === (int)$c->id)
                ->map(fn($r) => [
                    'id' => (int) $r->id,
                    'content' => $r->content,
                    'user_id' => (int) $r->user_id,
                    'created_at' => $r->created_at ? $r->created_at->toISOString() : null,
                    'user_name' => $r->user ? $r->user->full_name : "Người dùng #{$r->user_id}",
                    'user_email' => $r->user ? $r->user->email : "",
                    'user_role' => $r->user ? $r->user->role : "Giảng viên / Admin",
                    'user_avatar' => $r->user ? $r->user->avatar_url : null,
                ])
                ->sortBy(fn($r) => $r['created_at'])
                ->values()
                ->toArray();

            $replyCount = count($replies);
            $replyAuthorsCount = count(array_unique(array_column($replies, 'user_id')));
            $firstReplyAt = $replyCount > 0 ? $replies[0]['created_at'] : null;
            $latestReplyAt = $replyCount > 0 ? $replies[$replyCount - 1]['created_at'] : null;
            $latestReply = $replyCount > 0 ? $replies[$replyCount - 1] : null;

            $createdAtMs = $c->created_at ? $c->created_at->timestamp * 1000 : 0;
            $nowMs = $now->timestamp * 1000;
            $elapsedMs = max(0, $nowMs - $createdAtMs);
            $slaLimitMs = 24 * 3600 * 1000; // 24 hours

            $statusVal = $c->status ?: 'visible';
            $isResponseOverdue = ($replyCount === 0 && $statusVal === 'visible' && $elapsedMs > $slaLimitMs);
            $overdueHours = $isResponseOverdue ? (int) max(1, round(($elapsedMs - $slaLimitMs) / 3600000)) : 0;
            $overdueMinutes = $isResponseOverdue ? (int) max(1, round(($elapsedMs - $slaLimitMs) / 60000)) : 0;

            $firstResponseHours = null;
            $firstResponseMinutes = null;
            if ($firstReplyAt) {
                $firstMs = Carbon::parse($firstReplyAt)->timestamp * 1000;
                $firstDiff = max(0, $firstMs - $createdAtMs);
                $firstResponseHours = (int) round($firstDiff / 3600000);
                $firstResponseMinutes = (int) round($firstDiff / 60000);
            }

            $warningType = $evaluateWarningType($c->content);
            $isWarningUnresolved = ($warningType !== null && $statusVal === 'visible');
            $isRiskyContentVisible = ($warningType !== null && $statusVal === 'visible');
            $isHiddenUnresolved = ($statusVal === 'hidden');

            $isNeedsAction = ($isResponseOverdue || $isWarningUnresolved || $isRiskyContentVisible || $isHiddenUnresolved);

            $priorityLevel = 'normal';
            if ($warningType === 'offensive' && $statusVal === 'visible') {
                $priorityLevel = 'critical';
            } elseif (($warningType === 'spam' && $statusVal === 'visible') || ($isResponseOverdue && $overdueHours > 24)) {
                $priorityLevel = 'high';
            } elseif ($isResponseOverdue || $isHiddenUnresolved) {
                $priorityLevel = 'medium';
            }

            return [
                'id' => (int) $c->id,
                'target_type' => 'comment',
                'status' => $statusVal,
                'content' => $c->content,
                'rating' => null,
                'user_id' => (int) $c->user_id,
                'course_id' => $courseIdVal,
                'lesson_id' => $c->lesson_id ? (int) $c->lesson_id : null,
                'order_id' => $c->order_id ? (int) $c->order_id : null,
                'parent_id' => $c->parent_id ? (int) $c->parent_id : null,
                'created_at' => $c->created_at ? $c->created_at->toISOString() : null,
                'updated_at' => $c->updated_at ? $c->updated_at->toISOString() : null,
                'user' => $c->user ? [
                    'id' => (int) $c->user->id,
                    'full_name' => $c->user->full_name ?: $c->user->name,
                    'email' => $c->user->email,
                    'avatar_url' => $c->user->avatar_url,
                    'status' => $c->user->status,
                ] : null,
                'course' => $courseObj ? [
                    'id' => (int) $courseObj->id,
                    'title' => $courseObj->title,
                    'slug' => $courseObj->slug,
                ] : null,
                'lesson' => $c->lesson ? [
                    'id' => (int) $c->lesson->id,
                    'title' => $c->lesson->title,
                ] : null,
                'order' => null,
                'parent' => $c->parent ? [
                    'id' => (int) $c->parent->id,
                    'content' => $c->parent->content,
                    'user_id' => (int) $c->parent->user_id,
                ] : null,
                'replies' => $replies,
                'reply_count' => $replyCount,
                'reply_authors_count' => $replyAuthorsCount,
                'first_reply_at' => $firstReplyAt,
                'latest_reply_at' => $latestReplyAt,
                'latest_reply' => $latestReply,
                'first_response_hours' => $firstResponseHours,
                'first_response_minutes' => $firstResponseMinutes,
                'is_response_overdue' => $isResponseOverdue,
                'overdue_hours' => $overdueHours,
                'overdue_minutes' => $overdueMinutes,
                'warning_type' => $warningType,
                'is_warning_unresolved' => $isWarningUnresolved,
                'is_risky_content_visible' => $isRiskyContentVisible,
                'is_low_rating_unanswered' => false,
                'is_hidden_unresolved' => $isHiddenUnresolved,
                'is_needs_action' => $isNeedsAction,
                'priority_level' => $priorityLevel,
            ];
        });

        // Map reviews
        $reviewsList = $reviews->map(function ($r) use ($evaluateWarningType, $now) {
            $userObj = $r->order ? $r->order->user : null;
            $courseObj = $r->order ? $r->order->course : null;
            $statusVal = 'visible';

            $createdAtMs = $r->created_at ? $r->created_at->timestamp * 1000 : 0;
            $nowMs = $now->timestamp * 1000;
            $elapsedMs = max(0, $nowMs - $createdAtMs);
            $slaLimitMs = 48 * 3600 * 1000; // 48 hours

            $replies = [];
            $replyCount = 0;
            $replyAuthorsCount = 0;
            $firstReplyAt = null;

            $isResponseOverdue = ($replyCount === 0 && $statusVal === 'visible' && $elapsedMs > $slaLimitMs);
            $overdueHours = $isResponseOverdue ? (int) max(1, round(($elapsedMs - $slaLimitMs) / 3600000)) : 0;
            $overdueMinutes = $isResponseOverdue ? (int) max(1, round(($elapsedMs - $slaLimitMs) / 60000)) : 0;

            $warningType = $evaluateWarningType($r->comment);
            $isWarningUnresolved = ($warningType !== null && $statusVal === 'visible');
            $isRiskyContentVisible = ($warningType !== null && $statusVal === 'visible');
            $isLowRatingUnanswered = ($r->rating <= 2 && $statusVal === 'visible');
            $isHiddenUnresolved = false;

            $isNeedsAction = ($isResponseOverdue || $isLowRatingUnanswered || $isWarningUnresolved || $isRiskyContentVisible);

            $priorityLevel = 'normal';
            if ($warningType === 'offensive' && $statusVal === 'visible') {
                $priorityLevel = 'critical';
            } elseif (($warningType === 'spam' && $statusVal === 'visible') || $isLowRatingUnanswered || ($isResponseOverdue && $overdueHours > 24)) {
                $priorityLevel = 'high';
            } elseif ($isResponseOverdue) {
                $priorityLevel = 'medium';
            }

            return [
                'id' => (int) $r->id,
                'target_type' => 'review',
                'status' => $statusVal,
                'content' => $r->comment ?: '',
                'rating' => (int) $r->rating,
                'user_id' => $userObj ? (int) $userObj->id : null,
                'course_id' => $courseObj ? (int) $courseObj->id : null,
                'lesson_id' => null,
                'order_id' => $r->order_id ? (int) $r->order_id : null,
                'parent_id' => null,
                'created_at' => $r->created_at ? $r->created_at->toISOString() : null,
                'updated_at' => $r->updated_at ? $r->updated_at->toISOString() : null,
                'user' => $userObj ? [
                    'id' => (int) $userObj->id,
                    'full_name' => $userObj->full_name ?: $userObj->name,
                    'email' => $userObj->email,
                    'avatar_url' => $userObj->avatar_url,
                    'status' => $userObj->status,
                ] : null,
                'course' => $courseObj ? [
                    'id' => (int) $courseObj->id,
                    'title' => $courseObj->title,
                    'slug' => $courseObj->slug,
                ] : null,
                'lesson' => null,
                'order' => $r->order ? [
                    'id' => (int) $r->order->id,
                    'order_code' => $r->order->order_code,
                    'amount' => (string) $r->order->amount,
                    'status' => $r->order->status,
                    'payment_status' => $r->order->payment_status,
                    'paid_at' => $r->order->paid_at ? $r->order->paid_at->toISOString() : null,
                ] : null,
                'parent' => null,
                'replies' => $replies,
                'reply_count' => $replyCount,
                'reply_authors_count' => $replyAuthorsCount,
                'first_reply_at' => $firstReplyAt,
                'latest_reply_at' => null,
                'latest_reply' => null,
                'first_response_hours' => null,
                'first_response_minutes' => null,
                'is_response_overdue' => $isResponseOverdue,
                'overdue_hours' => $overdueHours,
                'overdue_minutes' => $overdueMinutes,
                'warning_type' => $warningType,
                'is_warning_unresolved' => $isWarningUnresolved,
                'is_risky_content_visible' => $isRiskyContentVisible,
                'is_low_rating_unanswered' => $isLowRatingUnanswered,
                'is_hidden_unresolved' => $isHiddenUnresolved,
                'is_needs_action' => $isNeedsAction,
                'priority_level' => $priorityLevel,
            ];
        });

        // 3. Union datasets
        $allItems = array_merge($commentsList->toArray(), $reviewsList->toArray());

        // 4. Compute Summary on FULL dataset
        $totalComments = count(array_filter($allItems, fn($i) => $i['target_type'] === 'comment'));
        $totalReviews = count(array_filter($allItems, fn($i) => $i['target_type'] === 'review'));
        $visibleComments = count(array_filter($allItems, fn($i) => $i['target_type'] === 'comment' && $i['status'] === 'visible'));
        $hiddenComments = count(array_filter($allItems, fn($i) => $i['target_type'] === 'comment' && $i['status'] === 'hidden'));
        $deletedComments = count(array_filter($allItems, fn($i) => $i['target_type'] === 'comment' && $i['status'] === 'deleted'));

        $visibleReviews = array_filter($allItems, fn($i) => $i['target_type'] === 'review' && $i['status'] === 'visible');
        $deletedReviews = count(array_filter($allItems, fn($i) => $i['target_type'] === 'review' && $i['status'] === 'deleted'));

        $averageRating = 0;
        if (count($visibleReviews) > 0) {
            $sumRating = array_reduce($visibleReviews, fn($sum, $r) => $sum + $r['rating'], 0);
            $averageRating = (float) number_format($sumRating / count($visibleReviews), 1);
        }

        $needActionCount = count(array_filter($allItems, fn($i) => $i['is_needs_action']));
        $violationCount = count(array_filter($allItems, fn($i) => $i['warning_type'] !== null));

        $summary = [
            'total_items' => count($allItems),
            'total_comments' => $totalComments,
            'total_reviews' => $totalReviews,
            'need_action_count' => $needActionCount,
            'violation_count' => $violationCount,
            'visible_comments' => $visibleComments,
            'hidden_comments' => $hiddenComments,
            'deleted_comments' => $deletedComments,
            'visible_reviews' => count($visibleReviews),
            'deleted_reviews' => $deletedReviews,
            'average_rating' => $averageRating,
        ];

        // 5. Apply filtering on unified list
        $filteredItems = $allItems;

        // A. Search
        if ($search !== '') {
            $filteredItems = array_filter($filteredItems, function ($item) use ($search) {
                $idMatch = str_contains((string) $item['id'], $search);
                $contentMatch = str_contains(mb_strtolower($item['content'], 'UTF-8'), $search);
                $userNameMatch = $item['user'] ? str_contains(mb_strtolower($item['user']['full_name'], 'UTF-8'), $search) : false;
                $userEmailMatch = $item['user'] ? str_contains(mb_strtolower($item['user']['email'], 'UTF-8'), $search) : false;
                $courseTitleMatch = $item['course'] ? str_contains(mb_strtolower($item['course']['title'], 'UTF-8'), $search) : false;

                return $idMatch || $contentMatch || $userNameMatch || $userEmailMatch || $courseTitleMatch;
            });
        }

        // B. User & Course Filter
        if ($userId !== null) {
            $filteredItems = array_filter($filteredItems, fn($item) => (int)$item['user_id'] === $userId);
        }
        if ($courseId !== null) {
            $filteredItems = array_filter($filteredItems, fn($item) => (int)$item['course_id'] === $courseId);
        }

        // C. Date Filter
        if ($timePreset !== 'all' || $dateFrom || $dateTo) {
            $fromDate = null;
            $toDate = null;

            if ($timePreset === 'today') {
                $fromDate = Carbon::now()->startOfDay();
                $toDate = Carbon::now()->endOfDay();
            } elseif ($timePreset === '7days') {
                $fromDate = Carbon::now()->subDays(7);
            } elseif ($timePreset === '1month') {
                $fromDate = Carbon::now()->subDays(30);
            } elseif ($timePreset === '3months') {
                $fromDate = Carbon::now()->subDays(90);
            }

            if ($dateFrom) {
                $fromDate = Carbon::parse($dateFrom)->startOfDay();
            }
            if ($dateTo) {
                $toDate = Carbon::parse($dateTo)->endOfDay();
            }

            $filteredItems = array_filter($filteredItems, function ($item) use ($fromDate, $toDate) {
                $itemDate = Carbon::parse($item['created_at']);
                if ($fromDate && $itemDate->lt($fromDate)) return false;
                if ($toDate && $itemDate->gt($toDate)) return false;
                return true;
            });
        }

        // D. Reply status / Priority filters
        if ($replyStatus !== 'all') {
            $filteredItems = array_filter($filteredItems, function ($i) use ($replyStatus) {
                if ($replyStatus === 'needs_action') {
                    return $i['is_needs_action'];
                } elseif ($replyStatus === 'violation') {
                    return $i['warning_type'] !== null;
                } elseif ($replyStatus === 'overdue_response' || $replyStatus === 'overdue') {
                    return $i['is_response_overdue'];
                } elseif ($replyStatus === 'low_rating_unanswered') {
                    return $i['is_low_rating_unanswered'];
                } elseif ($replyStatus === 'warning_unresolved') {
                    return $i['is_warning_unresolved'];
                } elseif ($replyStatus === 'risky_visible') {
                    return $i['is_risky_content_visible'];
                } elseif ($replyStatus === 'hidden_unresolved') {
                    return $i['is_hidden_unresolved'];
                } elseif ($replyStatus === 'multiple_replies') {
                    return $i['reply_count'] >= 2;
                } elseif ($replyStatus === 'answered') {
                    return $i['reply_count'] > 0;
                } elseif ($replyStatus === 'unanswered') {
                    return $i['reply_count'] === 0;
                }
                return true;
            });
        }

        // E. Target type
        if ($targetType !== 'all') {
            $filteredItems = array_filter($filteredItems, fn($item) => $item['target_type'] === $targetType);
        }

        // F. Status
        if ($status !== 'all') {
            $filteredItems = array_filter($filteredItems, fn($item) => $item['status'] === $status);
        }

        // G. Rating filter
        if ($rating !== 'all') {
            $filteredItems = array_filter($filteredItems, fn($item) => $item['target_type'] === 'review' && (int)$item['rating'] === (int)$rating);
        }

        // 6. Sort
        usort($filteredItems, function ($a, $b) use ($sortBy, $sortDirection) {
            $valA = null;
            $valB = null;

            if ($sortBy === 'user_name') {
                $valA = mb_strtolower($a['user']['full_name'] ?? '', 'UTF-8');
                $valB = mb_strtolower($b['user']['full_name'] ?? '', 'UTF-8');
            } elseif ($sortBy === 'course_title') {
                $valA = mb_strtolower($a['course']['title'] ?? '', 'UTF-8');
                $valB = mb_strtolower($b['course']['title'] ?? '', 'UTF-8');
            } else {
                $valA = $a[$sortBy] ?? null;
                $valB = $b[$sortBy] ?? null;

                if ($sortBy === 'created_at' || $sortBy === 'updated_at') {
                    $valA = $valA ? Carbon::parse($valA)->timestamp : 0;
                    $valB = $valB ? Carbon::parse($valB)->timestamp : 0;
                }
            }

            if ($valA === $valB) return 0;

            if ($sortDirection === 'asc') {
                return $valA > $valB ? 1 : -1;
            } else {
                return $valA < $valB ? 1 : -1;
            }
        });

        // 7. Paginate
        $total = count($filteredItems);
        $lastPage = (int) ceil($total / $perPage) ?: 1;
        $startIndex = ($page - 1) * $perPage;
        $paginatedItems = array_slice($filteredItems, $startIndex, $perPage);

        return [
            'summary' => $summary,
            'items' => $paginatedItems,
            'meta' => [
                'current_page' => $page,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'total' => $total,
            ],
        ];
    }

    public function getModerationItemDetail(string $targetType, int $id): array
    {
        $params = [
            'page' => 1,
            'per_page' => 99999,
            'target_type' => $targetType,
        ];
        $res = $this->getModerationItems($params);
        foreach ($res['items'] as $item) {
            if ((int)$item['id'] === $id) {
                return $item;
            }
        }
        throw new BusinessException("Không tìm thấy mục kiểm duyệt có ID {$id}.", 404);
    }
}
