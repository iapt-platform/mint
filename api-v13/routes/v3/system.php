<?php

/*
|--------------------------------------------------------------------------
| v3 · system —— 存活、版本、运维杂项
|--------------------------------------------------------------------------
|
| 由 routes/api.php 的 v3 组 require 进来，前缀 v3 与名字前缀 v3. 已在那边加好，
| 本文件只写路由本身。
|
*/

use App\Http\Controllers\V3\HeartbeatController;
use App\Http\Controllers\V3\UpgradeController;
use Illuminate\Support\Facades\Route;

// 存活检查是单例资源（只有一个实例），不是集合
Route::apiSingleton('heartbeat', HeartbeatController::class)->only(['show']);

Route::apiResource('upgrade', UpgradeController::class)->only(['index']);
