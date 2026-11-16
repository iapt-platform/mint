<?php

/*
|--------------------------------------------------------------------------
| v3 · admin —— 管理后台（仅 root / administrator）
|--------------------------------------------------------------------------
|
| 由 routes/api.php 的 v3 组 require 进来，前缀已在那边加好。
| 整组先过 auth.v3（没登录 401）再过 auth.v3.admin（不是管理员 403）。
|
*/

use App\Http\Controllers\V3\AdminAgentFeedbackController;
use App\Http\Controllers\V3\AdminAgentRunController;
use App\Http\Controllers\V3\AdminAgentStatsController;
use App\Http\Controllers\V3\AdminReadingStatsController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->as('admin.')->middleware(['auth.v3', 'auth.v3.admin'])->group(function () {
    Route::get('reading-stats', AdminReadingStatsController::class)->name('reading-stats');
    Route::get('agent-stats', AdminAgentStatsController::class)->name('agent-stats');

    Route::get('agent-feedback', [AdminAgentFeedbackController::class, 'index'])->name('agent-feedback.index');

    // digest 是字面量段，写在 {run} 之前
    Route::get('agent-runs/digest', [AdminAgentRunController::class, 'digest'])->name('agent-runs.digest');
    Route::get('agent-runs', [AdminAgentRunController::class, 'index'])->name('agent-runs.index');
    Route::get('agent-runs/{run}', [AdminAgentRunController::class, 'show'])->name('agent-runs.show')->whereUuid('run');
});
