<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * 成功响应
     */
    protected function ok(mixed $data = null, string $message = ''): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'data' => $data,
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
            'ok' => false,
            'data' => $data,
            'message' => $message,
        ], $status, [], JSON_UNESCAPED_UNICODE);
    }

    /*
    |--------------------------------------------------------------------------
    | v3 不需要任何响应 helper
    |--------------------------------------------------------------------------
    |
    | 成功：直接 return Resource。JsonResource 默认就包 data，paginator 交给
    | 框架算分页，形状是 {data: {...}} 与 {data: [...], meta: {...}}。
    |
    |     return ChannelV3Resource::make($channel);
    |     return ChannelV3Resource::collection($query->paginate($n));
    |
    | 失败：抛异常。abort()/ValidationException/BusinessException，
    | 由 bootstrap/app.php 统一渲染成 RFC 9457 Problem Details。
    |
    | 上面 ok()/error() 只给 v2 用，v3 控制器里不准出现。
    |
    */
}
