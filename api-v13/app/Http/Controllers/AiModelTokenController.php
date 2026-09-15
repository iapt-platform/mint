<?php

namespace App\Http\Controllers;

use App\Models\AiModel;
use App\Services\AuthService;
use App\Tools\OpsLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 签发 AI 模型的身份 token。
 *
 * 外部客户端（如 wikipali-write Skill）拿到该 token 后，即可以「模型身份」
 * 调用写入类端点，使 editor_uid 记为模型 uid 而非操作者本人。
 *
 * @see docs/wikipali-write-skill-design.md §5.1
 */
class AiModelTokenController extends Controller
{
    /**
     * 取得指定 AI 模型的 user token。
     *
     * 仅模型 owner 本人可调用（设计决策：模型只挂个人 studio，不支持 group studio）。
     * 签出的 token 有效期 30 天，可用 destroy() 提前撤销；属高敏感凭据，故记入 ops 日志。
     */
    public function show(Request $request, AiModel $aiModel): JsonResponse
    {
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), null, 401);
        }
        if (! AiModelController::canEdit($user['user_uid'], $aiModel->owner_id)) {
            return $this->error(__('auth.failed'), null, 403);
        }

        $token = AuthService::getUserToken($aiModel->uid);
        if (! $token) {
            return $this->error('ai model not found', null, 404);
        }

        OpsLog::debug($user['user_uid'], [
            'action' => 'ai-model-token.issue',
            'model_uid' => $aiModel->uid,
            'model_name' => $aiModel->name,
        ]);

        return $this->ok([
            'uid' => $aiModel->uid,
            'name' => $aiModel->name,
            'token' => $token,
        ]);
    }

    /**
     * 撤销该模型已签出的全部身份 token。
     *
     * 版本号自增后，旧 token 里的 ver 立即对不上（见 AuthService::current()）。
     * 无法只撤销其中一张——凭据泄漏时本就该全部作废。
     */
    public function destroy(Request $request, AiModel $aiModel): JsonResponse
    {
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), null, 401);
        }
        if (! AiModelController::canEdit($user['user_uid'], $aiModel->owner_id)) {
            return $this->error(__('auth.failed'), null, 403);
        }

        $aiModel->increment('token_version');

        OpsLog::debug($user['user_uid'], [
            'action' => 'ai-model-token.revoke',
            'model_uid' => $aiModel->uid,
            'model_name' => $aiModel->name,
            'token_version' => (int) $aiModel->token_version,
        ]);

        return $this->ok([
            'uid' => $aiModel->uid,
            'name' => $aiModel->name,
            'token_version' => (int) $aiModel->token_version,
        ]);
    }
}
