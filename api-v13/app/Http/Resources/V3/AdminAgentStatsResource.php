<?php

namespace App\Http\Resources\V3;

use App\Services\V3\AgentStatsService;
use Illuminate\Http\Request;

/**
 * 管理后台 AI 对话统计（一次返回整页数据）。口径见 {@see AgentStatsService}。
 */
class AdminAgentStatsResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{
     *     range: array{from: string, to: string, account_type: string},
     *     summary: array{runs: int, deep: int, passage: int, denied: int, errors: int, cancelled: int, unchecked: int, improve: int, askers: int, askers_user: int, askers_guest: int, latency_p50_ms: int|null, latency_p95_ms: int|null, tokens_in: int, tokens_out: int, feedback_up: int, feedback_down: int, feedback_asked: int},
     *     previous: array{runs: int, deep: int, passage: int, denied: int, errors: int, cancelled: int, unchecked: int, improve: int, askers: int, askers_user: int, askers_guest: int, latency_p50_ms: int|null, latency_p95_ms: int|null, tokens_in: int, tokens_out: int, feedback_up: int, feedback_down: int, feedback_asked: int},
     *     daily: array<int, array{date: string, question: int, term: int, passage: int, chat: int, none: int, denied: int, errors: int, latency_p50_ms: int|null, latency_p95_ms: int|null, feedback_up: int, feedback_down: int}>,
     *     clients: array<int, array{client: string, runs: int}>,
     *     tools: array<int, array{name: string, calls: int, errors: int, avg_ms: int}>,
     *     feedback_reasons: array<int, array{reason: string, count: int}>,
     *     credits: array{spent_free: int, spent_earned: int, refunded: int, earned_reading: int, earned_editing: int, adjusted: int}
     * }
     */
    public function toArray($request): array
    {
        return $this->resource;
    }
}
