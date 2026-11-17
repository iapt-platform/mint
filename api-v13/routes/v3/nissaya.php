<?php

/*
|--------------------------------------------------------------------------
| v3 · nissaya —— 缅文 nissaya（词尾、语法关系）
|--------------------------------------------------------------------------
|
| 由 routes/api.php 的 v3 组 require 进来，前缀已在那边加好。
|
*/

use App\Http\Controllers\V3\NissayaCardController;
use Illuminate\Support\Facades\Route;

// 缅文词尾卡片：只读、公开。取代 v2 nissaya-card
Route::get('nissaya-cards/{ending}', NissayaCardController::class)->name('nissaya-cards.show');
