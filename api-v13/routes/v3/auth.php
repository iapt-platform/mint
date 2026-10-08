<?php

/*
|--------------------------------------------------------------------------
| v3 · auth —— 账号：找回密码、注册、登录
|--------------------------------------------------------------------------
|
| 由 routes/api.php 的 v3 组 require 进来，前缀已在那边加好。
|
| 登录（sessions / me）还在 v2，见根目录 CLAUDE.md「认证迁移待办」。
|
*/

use App\Http\Controllers\V3\PasswordResetController;
use Illuminate\Support\Facades\Route;

// 找回密码。token 是 64 位字母数字，约束挂在每条路由上（prefix 组上的 where 会互相顶掉）。
// 只注册 PATCH 不注册 PUT：完成重置只改密码这一项，不是整体替换。
Route::post('password-resets', [PasswordResetController::class, 'store'])
    ->middleware('throttle:password-resets')
    ->name('password-resets.store');
Route::get('password-resets/{token}', [PasswordResetController::class, 'show'])
    ->where('token', '[A-Za-z0-9]{64}')
    ->name('password-resets.show');
Route::patch('password-resets/{token}', [PasswordResetController::class, 'update'])
    ->where('token', '[A-Za-z0-9]{64}')
    ->middleware('throttle:password-resets')
    ->name('password-resets.update');
