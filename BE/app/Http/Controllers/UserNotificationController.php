<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $userId = $user ? (int) $user->id : null;
        $status = $request->query('status'); // 'all', 'unread', 'read'
        $type = $request->query('type'); // 'all', 'system', 'course', 'promo'

        if ($userId) {
            $query = Notification::where('user_id', $userId);
        } else {
            $query = Notification::whereRaw('1 = 0');
        }

        if ($status === 'unread') {
            $query->whereNull('read_at');
        } elseif ($status === 'read') {
            $query->whereNotNull('read_at');
        }

        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }

        $notifications = $query->orderByDesc('created_at')->take(20)->get();

        $items = $notifications->map(function ($n) {
            $url = $n->action_url ?? '/courses';
            if (str_starts_with($url, '/me/courses/')) {
                $url = str_replace('/me/courses/', '/learn/', $url);
            } elseif (str_starts_with($url, '/orders/')) {
                $url = '/purchase-history';
            }
            return [
                'id' => $n->id,
                'type' => $n->type ?? 'info',
                'category' => $n->channel ?? 'system',
                'title' => $n->title,
                'message' => $n->message,
                'created_at' => $n->created_at->toIso8601String(),
                'time_ago' => $n->created_at->diffForHumans(),
                'is_read' => !is_null($n->read_at),
                'read_at' => $n->read_at ? $n->read_at->toIso8601String() : null,
                'action_url' => $url
            ];
        });

        return ApiResponse::success($items, 'Lấy danh sách thông báo thành công.');
    }

    public function readAll(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        return ApiResponse::success(null, 'Đánh dấu tất cả thông báo đã đọc thành công.');
    }

    public function read(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            Notification::where('id', $id)
                ->where('user_id', $user->id)
                ->update(['read_at' => now()]);
        }

        return ApiResponse::success(null, 'Đánh dấu thông báo đã đọc thành công.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            Notification::where('id', $id)
                ->where('user_id', $user->id)
                ->delete();
        }

        return ApiResponse::success(null, 'Xóa thông báo thành công.');
    }

    public function clearAll(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            Notification::where('user_id', $user->id)->delete();
        }

        return ApiResponse::success(null, 'Xóa tất cả thông báo thành công.');
    }
}
