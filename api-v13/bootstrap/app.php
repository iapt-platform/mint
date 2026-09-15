<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\OpsToken;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\UserOperation;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        // API 组中间件（来自原 Laravel 8 Kernel）
        $middleware->api(append: [
            UserOperation::class,
        ]);

        // web 组中间件
        $middleware->web(append: [
            SetLocale::class,
        ]);

        $middleware->alias([
            'ops.token' => OpsToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // api/* 一律回 JSON。默认只在客户端声明 Accept: application/json 时才回 JSON，
        // 否则校验失败会 302 跳首页——客户端跟随重定向就拿到一张 HTML，
        // 比拿到错误码更难排查。
        $exceptions->shouldRenderJsonWhen(
            fn ($request, Throwable $e): bool => $request->is('api/*') || $request->expectsJson()
        );
    })->create();
