<?php

declare(strict_types=1);

namespace Dev\Auth\Support;

use Illuminate\Http\JsonResponse;

final class ApiResponse
{
    public static function success(
        string $message,
        mixed $data = null,
        int $status = 200,
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public static function error(
        string $message,
        mixed $errors = null,
        int $status = 422,
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}