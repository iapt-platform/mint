<?php

use App\Exceptions\Problem;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\OpsToken;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\UserOperation;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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

        /*
        |----------------------------------------------------------------------
        | v3 的错误一律 RFC 9457 Problem Details
        |----------------------------------------------------------------------
        |
        | 控制器只管抛异常，格式化在这里统一做。**只接管 api/v3/**：v2 的错误
        | 形状（Laravel 默认的 {message, errors}、以及自写的 {ok:false,…}）在
        | dashboard-v4 下线前一个字节都不能变。
        |
        | 不需要为 ModelNotFoundException / AuthorizationException 单独注册：
        | Handler::prepareException() 在 render 回调之前就把它们分别转成了
        | NotFoundHttpException(404) 与 AccessDeniedHttpException(403)，
        | 会被下面的兜底按状态码正确处理；给它们写处理器反而是死代码。
        |
        | AuthenticationException 则不在那张转换表里，且默认行为会尝试重定向到
        | login 路由，所以必须显式处理。
        |
        */
        $isV3 = fn (Request $request): bool => $request->is('api/v3/*');

        $exceptions->render(function (ValidationException $e, Request $request) use ($isV3) {
            if ($isV3($request)) {
                return Problem::response(
                    $request, 422, 'validation-error', null,
                    $e->getMessage(), ['errors' => $e->errors()],
                );
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) use ($isV3) {
            if ($isV3($request)) {
                return Problem::response($request, 401, 'unauthenticated');
            }
        });

        $exceptions->render(function (Throwable $e, Request $request) use ($isV3) {
            if (! $isV3($request)) {
                return null;
            }

            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
            $slug = match (true) {
                $status === 404 => 'not-found',
                $status === 403 => 'forbidden',
                $status >= 500 => 'server-error',
                default => 'request-error',
            };
            // 生产环境不泄露 5xx 的内部信息
            $detail = ($status < 500 || config('app.debug')) ? $e->getMessage() : null;

            return Problem::response($request, $status, $slug, null, $detail);
        });
    })->create();
