<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\StoreAgentGrantRequest;
use App\Http\Resources\V3\AgentGrantResource;
use App\Services\V3\AgentGrantService;

/**
 * agent 写入授权：人类授权自己名下的 AI 模型代写某个 channel。
 *
 * 取代 `POST /v2/access-token`（`AccessTokenController@store`）的 agent 用途；v2 那份在
 * dashboard-v4 下线前原样保留。区别见 {@see AgentGrantService}。
 */
class AgentGrantController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly AgentGrantService $grants) {}

    /**
     * 签发 agent 写入授权
     *
     * 必须用人类的登录 token 调用（模型 token 403）；模型必须归当前用户所有，当前用户
     * 必须能编辑该 channel。授权 7 天有效，只能由指定的模型使用；撤销该模型的 token
     * （token_version 自增）时一并失效。
     *
     * 模型写入时用自己的模型 token 登录，并带请求头 `X-Agent-Grant: <token>`；
     * 写入的记录 editor 为授权人、co_editor 为模型，权限按授权人判断。
     *
     * @responseStatus 201 签发成功
     * @responseStatus 403 用模型 token 调用、模型不归当前用户，或当前用户不能编辑该 channel
     *
     * @bodyParam ai_model string required 被授权的 AI 模型 uid
     * @bodyParam target_type string required 授权范围的类型。Enum: channel
     * @bodyParam target_id string required channel 的 uid
     * @bodyParam book integer 限定 book，0 或不传表示不限。Default: 0
     */
    public function store(StoreAgentGrantRequest $request)
    {
        return AgentGrantResource::make($this->grants->issue(
            $this->currentUser($request)['user_uid'],
            $request->validated(),
        ))->response()->setStatusCode(201);
    }
}
