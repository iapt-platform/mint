<?php

/*
|--------------------------------------------------------------------------
| v3 · auth —— 账号：找回密码、注册、登录
|--------------------------------------------------------------------------
|
| 由 routes/api.php 的 v3 组 require 进来，前缀已在那边加好。
|
*/

use App\Http\Controllers\V3\EmailCertificationController;
use App\Http\Controllers\V3\InviteController;
use App\Http\Controllers\V3\MeController;
use App\Http\Controllers\V3\PasswordResetController;
use App\Http\Controllers\V3\SessionController;
use App\Http\Controllers\V3\UserController;
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

// 注册：发验证码 → 验证码换 invite（或邀请邮件里自带 invite）→ 凭 invite 建账号
Route::post('email-certifications', [EmailCertificationController::class, 'store'])
    ->middleware('throttle:email-certifications')
    ->name('email-certifications.store');
Route::post('invites', [InviteController::class, 'store'])
    ->middleware('throttle:sign-up')
    ->name('invites.store');
Route::get('invites/{invite}', [InviteController::class, 'show'])
    ->whereUuid('invite')
    ->name('invites.show');
Route::post('users', [UserController::class, 'store'])
    ->middleware('throttle:sign-up')
    ->name('users.store');

// 登录与「我是谁」。退出登录是客户端丢掉 token，服务端不存会话，所以没有 DELETE sessions
Route::post('sessions', [SessionController::class, 'store'])
    ->middleware('throttle:sessions')
    ->name('sessions.store');
Route::get('me', [MeController::class, 'show'])
    ->middleware('auth.v3')
    ->name('me.show');
