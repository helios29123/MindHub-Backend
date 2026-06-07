<?php

namespace App\Http\Middleware;

use App\Exceptions\BusinessException;
use App\Helpers\ApiResponse;
use App\Repositories\SessionRepository;
use App\Repositories\UserRepository;
use App\Services\AccessTokenService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateSessionToken
{
    public function __construct(
        private  AccessTokenService $accessTokenService,
        private  SessionRepository $sessionRepository,
        private  UserRepository $userRepository
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $plainAccessToken = $request->bearerToken();

            if (! $plainAccessToken) {
                return ApiResponse::error('Unauthenticated.', [], 401);
            }

            $tokenPayload = $this->accessTokenService->parseAccessToken($plainAccessToken);
            $session = $this->sessionRepository->findActiveById($tokenPayload['session_id']);

            if (! $session) {
                return ApiResponse::error('Unauthenticated.', [], 401);
            }

            $user = $this->userRepository->findById($tokenPayload['user_id']);

            if (! $user || ! $user->isActive()) {
                return ApiResponse::error('Unauthenticated.', [], 401);
            }

            if ((int) $session->user_id !== (int) $user->id) {
                return ApiResponse::error('Unauthenticated.', [], 401);
            }

            $request->setUserResolver(fn () => $user);
            $request->attributes->set('auth_session', $session);
            $request->attributes->set('auth_token_payload', $tokenPayload);

            return $next($request);
        } catch (BusinessException $exception) {
            return ApiResponse::error(
                $exception->getMessage(),
                $exception->getErrors(),
                $exception->getStatusCode()
            );
        }
    }
}
