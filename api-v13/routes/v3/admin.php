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
use App\Http\Controllers\V3\AdminCreditAccountController;
use App\Http\Controllers\V3\AdminCreditAdjustmentController;
use App\Http\Controllers\V3\AdminCreditLedgerController;
use App\Http\Controllers\V3\AdminCreditOverviewController;
use App\Http\Controllers\V3\AdminCreditSettlementController;
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

    // AI 对话积分管理：概览（单例）、账号一览、流水、手工加减、手工结算（每日任务出问题时用）
    Route::get('credit-overview', AdminCreditOverviewController::class)->name('credit-overview');
    Route::get('credit-accounts', [AdminCreditAccountController::class, 'index'])->name('credit-accounts.index');
    Route::get('credit-ledger', [AdminCreditLedgerController::class, 'index'])->name('credit-ledger.index');
    Route::post('credit-adjustments', [AdminCreditAdjustmentController::class, 'store'])->name('credit-adjustments.store');
    Route::post('credit-settlements', [AdminCreditSettlementController::class, 'store'])->name('credit-settlements.store');
});
