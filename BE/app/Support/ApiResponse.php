<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiResponse
{
    public static function success(mixed $data = [], string $message = 'Lấy dữ liệu thành công', int $status = 200, ?array $meta = null): JsonResponse
    {
        if ($data instanceof JsonResource || $data instanceof AnonymousResourceCollection) {
            $data = $data->resolve();
        }

        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if ($meta !== null) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $status);
    }

    public static function paginated(AnonymousResourceCollection $resourceCollection, LengthAwarePaginator $paginator, string $message = 'Lấy dữ liệu thành công'): JsonResponse
    {
        return self::success(
            $resourceCollection->resolve(),
            $message,
            200,
            PaginationMeta::fromPaginator($paginator)
        );
    }

    public static function error(string $message, array $errors = [], int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
