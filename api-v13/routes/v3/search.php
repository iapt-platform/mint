<?php

/*
|--------------------------------------------------------------------------
| v3 · search —— 全文检索与检索建议
|--------------------------------------------------------------------------
|
| 由 routes/api.php 的 v3 组 require 进来，前缀已在那边加好。
|
*/

use App\Http\Controllers\V3\SearchController;
use App\Http\Controllers\V3\SearchSuggestionController;
use Illuminate\Support\Facades\Route;

// 只注册真正实现了的动作——空方法会在 OpenAPI 里变成幽灵端点
Route::apiResource('search', SearchController::class)->only(['index', 'store', 'show']);
Route::get('search-suggestions', SearchSuggestionController::class)->name('search-suggestions.index');
