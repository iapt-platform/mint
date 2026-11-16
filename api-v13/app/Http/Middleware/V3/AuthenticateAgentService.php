<?php

namespace App\Http\Middleware\V3;

use App\Services\V3\AgentTraceService;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * v3 服务间鉴权：只放行 AI 对话服务（agent-poc backend）。
 *
 * 请求头 `X-Agent-Service-Key` 与 config('mint.app.agent_service_key')（.env 的
 * AGENT_SERVICE_KEY）比对。密钥没配时一律拒绝，不会因为两边都是空串而放行。
 *
 * 它只证明「这是 agent 服务」；终端用户是谁另由 bearer（agent 透传的用户 token）
 * 决定，见 {@see AgentTraceService::record()}。
 *
 * 别名 `auth.agent-service`（在 bootstrap/app.php 注册）。
 */
class AuthenticateAgentService
{
    public const HEADER = 'X-Agent-Service-Key';

    /**
     * @throws AuthenticationException 密钥缺失或不匹配
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('mint.app.agent_service_key');
        $given = (string) $request->header(self::HEADER, '');

        if ($expected === '' || $given === '' || ! hash_equals($expected, $given)) {
            throw new AuthenticationException;
        }

        return $next($request);
    }
}
