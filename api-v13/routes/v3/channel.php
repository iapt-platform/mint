<?php

/*
|--------------------------------------------------------------------------
| v3 · channel —— 译文集（界面叫「版本风格」）与翻译进度
|--------------------------------------------------------------------------
|
| 由 routes/api.php 的 v3 组 require 进来，前缀已在那边加好。
|
| 阅读领域的 channel 查询（某章 / 某段有哪些 channel、有译文的 channel 目录）在 tipitaka.php。
|
*/

use App\Http\Controllers\V3\ChannelBatchProgressController;
use App\Http\Controllers\V3\ChannelController;
use App\Http\Controllers\V3\EditableChannelController;
use App\Http\Controllers\V3\ProgressController;
use App\Http\Controllers\V3\StudioChannelController;
use Illuminate\Support\Facades\Route;

Route::apiResource('progress', ProgressController::class)->only(['index']);

// 字面量段必须注册在 apiResource 之前，否则 {channel} 会把它们当成 uuid 吃掉
Route::get('channels/editable', EditableChannelController::class)
    ->name('channels.editable')
    ->middleware('auth.v3');
// 一批句子在各 channel 的完成度。POST：一章几百个句子 id 放不进查询串
Route::post('channels/batch-progress', ChannelBatchProgressController::class)
    ->name('channels.batch-progress');

// 读公开（可选登录），写要登录。只注册 PATCH，不注册 PUT（v3-resource skill 硬规范第 10 条）
Route::apiResource('channels', ChannelController::class)
    ->only(['index', 'show', 'destroy'])
    ->middlewareFor(['destroy'], 'auth.v3')
    ->whereUuid('channel');
Route::patch('channels/{channel}', [ChannelController::class, 'update'])
    ->name('channels.update')
    ->middleware('auth.v3')
    ->whereUuid('channel');

// 某个 studio（用户或团队，一律 uuid）的工作区列表与新建
Route::get('studios/{studio}/channels', [StudioChannelController::class, 'index'])
    ->name('studios.channels.index')
    ->middleware('auth.v3')
    ->whereUuid('studio');
Route::post('studios/{studio}/channels', [StudioChannelController::class, 'store'])
    ->name('studios.channels.store')
    ->middleware('auth.v3')
    ->whereUuid('studio');
