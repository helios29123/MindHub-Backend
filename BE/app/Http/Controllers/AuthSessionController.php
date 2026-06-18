<?php
namespace App\Http\Controllers;
use App\Http\Requests\Auth\ListSessionRequest;
use App\Http\Requests\Auth\LogoutAllRequest;
use App\Http\Requests\Auth\RevokeSessionRequest;
use App\Http\Resources\Auth\AuthSessionActionResource;
use App\Http\Resources\Auth\AuthSessionResource;
use App\Services\Auth\AuthSessionService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
final class AuthSessionController extends Controller
{
    public function __construct(
        private readonly AuthSessionService $authSessionService
    ) {
    }
    public function index(ListSessionRequest $request): JsonResponse
    {
        $sessions = $this->authSessionService->paginateForCurrentUser(
            userId: (int) $request->user()->getAuthIdentifier(),
            filters: $request->validated()
        );
        return ApiResponse::paginated(
            resourceCollection: AuthSessionResource::collection($sessions),
            paginator: $sessions,
            message: 'Lấy dữ liệu thành công.'
        );
    }
    public function revoke(RevokeSessionRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $result = $this->authSessionService->revokeForCurrentUser(
            userId: (int) $request->user()->getAuthIdentifier(),
            sessionId: (int) $validated['session_id']
        );
        $message = ((int) $result['revoked_count']) > 0
            ? 'Đăng xuất khỏi thiết bị thành công.'
            : 'Phiên đăng nhập đã được đăng xuất trước đó.';
        return ApiResponse::success(
            data: new AuthSessionActionResource($result),
            message: $message,
            status: 200
        );
    }
    public function logoutAll(LogoutAllRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $currentSession = $request->attributes->get('auth_session');
        $result = $this->authSessionService->logoutAllForCurrentUser(
            userId: (int) $request->user()->getAuthIdentifier(),
            currentSessionId: (int) ($currentSession?->id ?? 0),
            keepCurrent: (bool) ($validated['keep_current'] ?? false)
        );
        return ApiResponse::success(
            data: new AuthSessionActionResource($result),
            message: 'Đăng xuất toàn bộ thiết bị thành công.',
            status: 200
        );
    }
}