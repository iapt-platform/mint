<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * RFC 9457 Problem Details 响应的唯一构造点。
 *
 * v3 端点的所有错误都从这里出去：bootstrap/app.php 的异常处理器、
 * 以及自带 render() 的 BusinessException。控制器不要直接调用它——
 * 控制器只负责抛异常。
 */
class Problem
{
    /**
     * 状态码 → 稳定的英文 title。
     *
     * 按 RFC 9457，title 应在同类问题的所有场合保持一致、不随语言变化，
     * 便于机器识别；面向人的文案放 detail，由调用方传入已 __() 的字符串。
     */
    private const TITLES = [
        400 => 'Bad request.',
        401 => 'Authentication required.',
        403 => 'Forbidden.',
        404 => 'Resource not found.',
        405 => 'Method not allowed.',
        409 => 'Conflict.',
        419 => 'Page expired.',
        422 => 'Your request is not valid.',
        429 => 'Too many requests.',
        500 => 'Server error.',
        503 => 'Service unavailable.',
    ];

    public static function titleFor(int $status): string
    {
        return self::TITLES[$status] ?? (
            $status >= 500 ? self::TITLES[500] : self::TITLES[400]
        );
    }

    /**
     * @param  string  $slug  问题类型标识，进 type 字段
     * @param  array<string, mixed>  $extra  扩展成员，如 ['errors' => [...]]
     */
    public static function response(
        Request $request,
        int $status,
        string $slug,
        ?string $title = null,
        ?string $detail = null,
        array $extra = []
    ): JsonResponse {
        $body = array_filter([
            // 刻意用 URN 而不是 url()：绝对地址由 APP_URL 拼出，
            // 反向代理下容易拼错，而且目前没有文档页可供解引用。
            'type' => "urn:problem:{$slug}",
            'title' => $title ?? self::titleFor($status),
            'status' => $status,
            'detail' => $detail,
            'instance' => $request->getRequestUri(),
        ], fn ($v) => $v !== null && $v !== '') + $extra;

        return response()->json(
            $body,
            $status,
            ['Content-Type' => 'application/problem+json'],
            JSON_UNESCAPED_UNICODE
        );
    }
}
