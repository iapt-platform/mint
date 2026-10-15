<?php

namespace App\Services\V3;

use App\Http\Api\ChannelApi;
use App\Models\AiModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

/**
 * agent 协作：人类授权 AI 模型代自己写入（`/v3/agent-grants`），以及写入时认出「谁授权、谁代写」。
 *
 * 流程（仿 git 的 Co-Authored-By，记录见根目录 CLAUDE.md「agent 协作」）：
 * 1. 人类用自己的登录 token 为「自己名下的某个模型 + 某个 channel（可限 book）」签一张授权；
 * 2. 模型用自己的模型 token 登录，写入时在 `X-Agent-Grant` 头里带上这张授权；
 * 3. 服务端记 editor = 授权人（对内容负责）、co_editor = 模型，权限按授权人判，
 *    并且只在授权覆盖的 channel / book 内有效。
 *
 * 与 v2 `POST /v2/access-token` 的区别：
 * - 授权人、模型、范围全由服务端写进 token，不收客户端拼的载荷；
 * - 绑定具体模型（act），换个身份拿去用无效；
 * - 带模型的 token_version（ver）：撤销模型 token 时，它的授权一并失效；
 * - 用独立的签名密钥，v2 签出的 access token 在这里一律无效（v2 的载荷由客户端决定，
 *   可以伪造任何声明）。
 */
class AgentGrantService
{
    /** 授权有效期：一次批量写入作业足够，又把泄漏窗口限制住（与 v2 access token 相同） */
    public const TTL = 60 * 60 * 24 * 7;

    /** 写入请求里携带授权的请求头 */
    public const HEADER = 'X-Agent-Grant';

    private const TYPE = 'agent-grant';

    /**
     * 人类为自己名下的模型签发 channel 写入授权。
     *
     * @param  array{ai_model: string, target_type: string, target_id: string, book?: int}  $data
     * @return array{token: string, grantor: string, ai_model: string, target_type: string, target_id: string, book: int, expires_at: string}
     */
    public function issue(string $userUid, array $data): array
    {
        if ($this->isModel($userUid)) {
            // 授权必须出自人类：模型不能给自己（或别的模型）签授权
            throw new AuthorizationException(__('messages.agent_grant_by_model'));
        }
        $model = AiModel::where('uid', $data['ai_model'])->first(['uid', 'owner_id', 'token_version']);
        if (! $model) {
            throw ValidationException::withMessages(['ai_model' => __('messages.agent_grant_model_not_found')]);
        }
        // 与 v2 取模型 token 同一口径（AiModelController::canEdit）：模型归属于当前用户
        if ($model->owner_id !== $userUid) {
            throw new AuthorizationException(__('messages.agent_grant_not_owner'));
        }
        if (! ChannelApi::userCanEdit($userUid, $data['target_id'])) {
            throw new AuthorizationException(__('messages.agent_grant_no_power'));
        }

        $book = (int) ($data['book'] ?? 0);
        $expiresAt = time() + self::TTL;
        $token = JWT::encode([
            'typ' => self::TYPE,
            'grantor' => $userUid,
            'act' => $model->uid,
            'ver' => (int) $model->token_version,
            'res_type' => $data['target_type'],
            'res_id' => $data['target_id'],
            'book' => $book,
            'nbf' => time(),
            'exp' => $expiresAt,
        ], $this->key(), 'HS512');

        return [
            'token' => $token,
            'grantor' => $userUid,
            'ai_model' => $model->uid,
            'target_type' => $data['target_type'],
            'target_id' => $data['target_id'],
            'book' => $book,
            'expires_at' => gmdate('Y-m-d\TH:i:s\Z', $expiresAt),
        ];
    }

    /**
     * 写入请求的署名：人类直接写 → editor 是自己；模型代写 → editor 是授权人、co_editor 是模型。
     *
     * 模型没带授权一律 403：v3 的写入必须有一个对内容负责的人。人类带了授权 422（授权只给模型用）。
     *
     * @return array{editor: string, co_editor: string|null, scope: array{channel: string, book: int}|null}
     */
    public function actor(string $userUid, ?string $grant): array
    {
        $isModel = $this->isModel($userUid);
        if (! $isModel) {
            if ($grant !== null && $grant !== '') {
                throw ValidationException::withMessages([self::HEADER => __('messages.agent_grant_human')]);
            }

            return ['editor' => $userUid, 'co_editor' => null, 'scope' => null];
        }
        if ($grant === null || $grant === '') {
            throw new AuthorizationException(__('messages.agent_grant_required'));
        }

        try {
            $claims = JWT::decode($grant, new Key($this->key(), 'HS512'));
        } catch (\Throwable) {
            // 伪造、过期、未生效、密钥不对，一律当作没有授权
            throw new AuthorizationException(__('messages.agent_grant_invalid'));
        }
        $version = AiModel::where('uid', $userUid)->value('token_version');
        if (($claims->typ ?? null) !== self::TYPE
            || ($claims->act ?? null) !== $userUid
            || (int) ($claims->ver ?? -1) !== (int) $version
            || ($claims->res_type ?? null) !== 'channel'
            || empty($claims->grantor)) {
            throw new AuthorizationException(__('messages.agent_grant_invalid'));
        }

        return [
            'editor' => (string) $claims->grantor,
            'co_editor' => $userUid,
            'scope' => ['channel' => (string) $claims->res_id, 'book' => (int) ($claims->book ?? 0)],
        ];
    }

    /**
     * 授权是否覆盖这次写入落到的 channel / book。人类直接写（scope 为 null）恒为真。
     *
     * @param  array{channel: string, book: int}|null  $scope
     */
    public function covers(?array $scope, ?string $channelUid, ?int $book): bool
    {
        if ($scope === null) {
            return true;
        }

        return $channelUid === $scope['channel'] && ($scope['book'] === 0 || $book === $scope['book']);
    }

    private function isModel(string $uid): bool
    {
        return AiModel::where('uid', $uid)->exists();
    }

    /** 与登录 token 分开的签名密钥：授权 token 不能被当成登录 token 用，反之亦然 */
    private function key(): string
    {
        return hash('sha512', config('mint.app.jwt_secrets_key').'|'.self::TYPE);
    }
}
