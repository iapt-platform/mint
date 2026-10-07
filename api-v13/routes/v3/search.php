<?php

/*
|--------------------------------------------------------------------------
| v3 · search —— 全文检索与检索建议
|--------------------------------------------------------------------------
|
| 由 routes/api.php 的 v3 组 require 进来，前缀已在那边加好。
|
*/

use App\Http\Controllers\V3\SearchPlusController;
use App\Http\Controllers\V3\SearchSuggestController;
use Illuminate\Support\Facades\Route;

// 只注册真正实现了的动作——空方法会在 OpenAPI 里变成幽灵端点
Route::apiResource('search', SearchPlusController::class)->only(['index', 'store', 'show']);
Route::apiResource('search-suggest', SearchSuggestController::class)->only(['index']);
