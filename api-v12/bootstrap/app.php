<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware): void {

        // API 组中间件（来自原 Laravel 8 Kernel）
        $middleware->api(append: [
            \App\Http\Middleware\ApiLog::class,
            \App\Http\Middleware\UserOperation::class,
        ]);

        // web 组中间件
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
