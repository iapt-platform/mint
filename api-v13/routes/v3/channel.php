<?php

/*
|--------------------------------------------------------------------------
| v3 · channel —— 译本与翻译进度
|--------------------------------------------------------------------------
|
| 由 routes/api.php 的 v3 组 require 进来，前缀已在那边加好。
|
| channel 本身还没迁（见 CLAUDE.md 的 v3 重构一节），这里先只有 progress。
|
*/

use App\Http\Controllers\V3\ProgressController;
use Illuminate\Support\Facades\Route;

// FIXME: ProgressController@index 还带着 switch($request->input('view'))，
// 不符合 v3 规范（v3 端点不准有 view 开关）。迁 channel 时一并处理。
Route::apiResource('progress', ProgressController::class)->only(['index']);
