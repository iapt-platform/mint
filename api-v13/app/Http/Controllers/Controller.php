<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * 成功响应
     */
    protected function ok(mixed $data = null, string $message = ''): JsonResponse
    {
        return response()->json([
            'ok'      => true,
            'data'    => $data,
            'message' => $message,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 错误响应
     */
    protected function error(
        string $message,
        mixed $data = null,
        int $status = 400
    ): JsonResponse {
        return response()->json([
            'ok'      => false,
            'data'    => $data,
            'message' => $message,
        ], $status, [], JSON_UNESCAPED_UNICODE);
    }
}
