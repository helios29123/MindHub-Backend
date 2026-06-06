<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\BusinessException;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\AuthResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserResources;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->register($request->validated());

            return ApiResponse::success(
                'Đăng ký tài khoản thành công.',
                [
                    'user' => new UserResources($user),
                ],
                201
            );
        } catch (BusinessException $exception) {
            return ApiResponse::error(
                $exception->getMessage(),
                $exception->getErrors(),
                $exception->getStatusCode()
            );
        }
    }


}
