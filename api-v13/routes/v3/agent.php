<?php

/*
|--------------------------------------------------------------------------
| v3 · agent —— AI 对话（研究过程入库、会话历史）
|--------------------------------------------------------------------------
|
| 由 routes/api.php 的 v3 组 require 进来，前缀已在那边加好。
|
| 额度 / 积分（agent-charges、agent-quota、credit-policy、reading-time）也在这里。
|
*/

use App\Http\Controllers\V3\AgentChargeController;
use App\Http\Controllers\V3\AgentEventController;
use App\Http\Controllers\V3\AgentFeedbackController;
use App\Http\Controllers\V3\AgentQuotaController;
use App\Http\Controllers\V3\AgentTraceController;
use App\Http\Controllers\V3\CreditPolicyController;
use App\Http\Controllers\V3\MeAgentThreadArchiveController;
use App\Http\Controllers\V3\MeAgentThreadController;
use App\Http\Controllers\V3\MeReadingTimeController;
use Illuminate\Support\Facades\Route;

// 研究过程入库：服务间接口，只认 agent 服务密钥；终端用户由透传的 bearer 决定
Route::post('agent-traces', [AgentTraceController::class, 'store'])
    ->middleware('auth.agent-service');

// 提问扣费：服务间接口，同上
Route::post('agent-charges', [AgentChargeController::class, 'store'])
    ->middleware('auth.agent-service');

// 额度（单例）：可选登录，游客带 device_id
Route::get('agent-quota', AgentQuotaController::class);

// 积分策略（单例）：公开
Route::get('credit-policy', CreditPolicyController::class);

// 回答评价与隐式信号：可选登录（游客带 device_id），限流防刷
Route::post('agent-feedback', [AgentFeedbackController::class, 'store'])->middleware('throttle:60,1');
Route::delete('agent-feedback/{message}', [AgentFeedbackController::class, 'destroy'])
    ->middleware('throttle:60,1')
    ->where('message', '[A-Za-z0-9_\-]{1,64}');
Route::post('agent-events', [AgentEventController::class, 'store'])->middleware('throttle:120,1');

// 我的会话历史 / 读经时长：全线登录
Route::prefix('me')->as('me.')->middleware('auth.v3')->group(function () {
    // 单例子资源 archive 是字面量段，写在 apiResource 之前
    Route::put('agent-threads/{thread}/archive', [MeAgentThreadArchiveController::class, 'update'])
        ->whereUuid('thread');
    Route::delete('agent-threads/{thread}/archive', [MeAgentThreadArchiveController::class, 'destroy'])
        ->whereUuid('thread');

    // 只注册 PATCH，不注册 PUT：v3 的更新只做局部更新（v3-resource skill 硬规范第 10 条）
    Route::apiResource('agent-threads', MeAgentThreadController::class)
        ->only(['index', 'show', 'destroy'])
        ->parameters(['agent-threads' => 'thread'])
        ->whereUuid('thread');
    Route::patch('agent-threads/{thread}', [MeAgentThreadController::class, 'update'])
        ->whereUuid('thread');

    Route::post('reading-time', [MeReadingTimeController::class, 'store']);
});
