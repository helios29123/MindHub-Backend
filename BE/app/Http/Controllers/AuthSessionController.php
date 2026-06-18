<?php
namespace App\Http\Controllers;
use App\Http\Requests\Auth\ListSessionRequest;
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
}