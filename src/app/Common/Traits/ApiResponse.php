<?php

namespace App\Common\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    public function apiResponse(mixed $data, string $message = '', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => $statusCode < 400,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }
}
