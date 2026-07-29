<?php

namespace App\Http\Controllers;

use App\Http\Resources\Instructor\InstructorNotificationResource;
use App\Models\Notification;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class InstructorNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = (int) $request->user()->id;
        $perPage = min(max((int) ($request->query('per_page') ?? 10), 1), 100);

        $query = Notification::where('user_id', $userId);

        if ($request->query('status') === 'unread') {
            $query->whereNull('read_at');
        } elseif ($request->query('status') === 'read') {
            $query->whereNotNull('read_at');
        }

        $paginator = $query->orderByDesc('created_at')->paginate($perPage);

        return ApiResponse::success(
            InstructorNotificationResource::collection(collect($paginator->items()))->resolve($request),
            'Lấy danh sách thông báo thành công.',
            200,
            [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        );
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $userId = (int) $request->user()->id;
        $count = Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();

        return ApiResponse::success(
            ['unread_count' => $count],
            'Lấy số thông báo chưa đọc thành công.'
        );
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $userId = (int) $request->user()->id;
        $notification = Notification::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$notification) {
            return ApiResponse::error('Không tìm thấy thông báo.', [], 404);
        }

        if ($notification->read_at === null) {
            $notification->read_at = now();
            $notification->save();
        }

        return ApiResponse::success(
            new InstructorNotificationResource($notification),
            'Lấy chi tiết thông báo thành công.'
        );
    }

    public function read(Request $request, int $id): JsonResponse
    {
        $userId = (int) $request->user()->id;
        $notification = Notification::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$notification) {
            return ApiResponse::error('Không tìm thấy thông báo.', [], 404);
        }

        $notification->read_at = now();
        $notification->save();

        return ApiResponse::success(
            new InstructorNotificationResource($notification),
            'Đánh dấu thông báo đã đọc thành công.'
        );
    }

    public function readAll(Request $request): JsonResponse
    {
        $userId = (int) $request->user()->id;
        Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return ApiResponse::success(
            null,
            'Đánh dấu tất cả thông báo đã đọc thành công.'
        );
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $userId = (int) $request->user()->id;
        $notification = Notification::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$notification) {
            return ApiResponse::error('Không tìm thấy thông báo.', [], 404);
        }

        $notification->delete();

        return ApiResponse::success(
            null,
            'Xóa thông báo thành công.'
        );
    }
}
