<?php

/*
|--------------------------------------------------------------------------
| v3 · interaction —— 用户对内容的互动
|--------------------------------------------------------------------------
|
| 由 routes/api.php 的 v3 组 require 进来，前缀已在那边加好。
|
| 将来 discussion、notification、tag 也落在这个文件。
|
*/

use App\Http\Controllers\V3\MeReactionController;
use App\Http\Controllers\V3\ReactionController;
use App\Http\Controllers\V3\ReactionTallyController;
use Illuminate\Support\Facades\Route;

// reactions（点赞/收藏/书签/关注/下载记录）。资源改名是 CLAUDE.md
// 「已登记的改名例外」里的唯一一条，底层仍是 v2 的 likes 表。

// tally 要注册在 apiResource 之前：将来给 reactions 补上 show 之后，
// {reaction} 会把 tally 当成 uid 吃掉
Route::get('reactions/tally', [ReactionTallyController::class, 'index']);

// 公共只读：某个 target 下的 reactions
Route::apiResource('reactions', ReactionController::class)->only(['index']);

// 当前用户自己的 reactions：全线登录，由 auth.v3 中间件在进控制器前挡掉
Route::prefix('me')->as('me.')->middleware('auth.v3')->group(function () {
    Route::apiResource('reactions', MeReactionController::class)->only(['index', 'store', 'destroy']);
});
