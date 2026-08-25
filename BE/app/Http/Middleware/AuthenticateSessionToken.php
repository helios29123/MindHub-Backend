<?php
namespace App\Http\Middleware;
use App\Exceptions\BusinessException;
use App\Repositories\Auth\SessionRepository;
use App\Repositories\User\UserRepository;
use App\Services\Auth\AccessTokenService;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class AuthenticateSessionToken
{
    public function __construct(
        private readonly AccessTokenService $accessTokenService,
        private readonly SessionRepository $sessionRepository,
        private readonly UserRepository $userRepository
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $plainAccessToken = $request->bearerToken();
            if (! $plainAccessToken) {
                $user = Auth::user() ?: $request->user();
                if ($user) {
                    $request->setUserResolver(fn () => $user);
                    return $next($request);
                }
                return ApiResponse::error('Unauthenticated.', [], 401);
            }

            $tokenPayload = $this->accessTokenService->parseAccessToken($plainAccessToken);
            $session = $this->sessionRepository->findActiveById(
                (int) $tokenPayload['session_id']
            );

            if (! $session) {
                $user = Auth::user() ?: $request->user();
                if ($user) {
                    $request->setUserResolver(fn () => $user);
                    return $next($request);
                }
                return ApiResponse::error('Unauthenticated.', [], 401);
            }

            $user = $this->userRepository->findById(
                (int) $tokenPayload['user_id']
            );

            if (! $user) {
                $user = Auth::user() ?: $request->user();
                if ($user) {
                    $request->setUserResolver(fn () => $user);
                    return $next($request);
                }
                return ApiResponse::error('Unauthenticated.', [], 401);
            }

            if ((int) $session->user_id !== (int) $user->id) {
                return ApiResponse::error('Unauthenticated.', [], 401);
            }

            $request->setUserResolver(fn () => $user);
            $request->attributes->set('auth_session', $session);
            $request->attributes->set('auth_token_payload', $tokenPayload);
            $request->setUserResolver(fn () => $user);
            $request->attributes->set('auth_session', $session);
            $request->attributes->set('auth_token_payload', $tokenPayload);
            return $next($request);
        } catch (\App\Exceptions\BusinessException $exception) {
            return ApiResponse::error(
                $exception->getMessage(),
                $exception->getErrors(),
                $exception->getStatusCode()
            );
        } catch (\Throwable $e) {
            return ApiResponse::error('Unauthenticated.', [], 401);
        }
    }
}