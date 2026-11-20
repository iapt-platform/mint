<?php

/*
|--------------------------------------------------------------------------
| v3 · markup —— 正文源码的渲染
|--------------------------------------------------------------------------
|
| 由 routes/api.php 的 v3 组 require 进来，前缀已在那边加好。
|
*/

use App\Http\Controllers\V3\MarkupPreviewController;
use Illuminate\Support\Facades\Route;

// 源码预览：不写库，要登录
Route::post('markup-previews', MarkupPreviewController::class)->middleware('auth.v3')->name('markup-previews.store');
