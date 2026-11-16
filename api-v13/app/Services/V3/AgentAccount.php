<?php

namespace App\Services\V3;

use App\Models\Concerns\OwnedByAgentAccount;

/**
 * AI 对话与积分里的「账号」：谁在提问、额度和积分算谁的。由 {@see AgentAccountService::resolve()} 得出。
 *
 * - 登录用户：type = user，id = 用户 uuid（user_infos.userid）；
 * - 游客：type = guest，id = 设备匿名 uuid（客户端生成）。
 *
 * 落库是两列 `account_type` + `account_id`。**两列必须一起用**：设备 id 是客户端自己填的，
 * 可以故意填成某个用户的 uid，只按 account_id 查就会把游客认成那个用户。
 * 所以按账号查询一律走模型的 `ownedBy()` scope（{@see OwnedByAgentAccount}），
 * 写入一律用 {@see columns()}，不要手写 `where('account_id', …)`。
 */
final readonly class AgentAccount
{
    public const TYPES = ['user', 'guest'];

    /**
     * @param  'user'|'guest'  $type
     */
    private function __construct(public string $type, public string $id) {}

    public static function user(string $userUid): self
    {
        return new self('user', $userUid);
    }

    public static function guest(string $deviceId): self
    {
        return new self('guest', $deviceId);
    }

    public function isUser(): bool
    {
        return $this->type === 'user';
    }

    /**
     * 登录用户的 uuid；游客为 null。
     */
    public function userUid(): ?string
    {
        return $this->isUser() ? $this->id : null;
    }

    /**
     * 写库用的两列。
     *
     * @return array{account_type: string, account_id: string}
     */
    public function columns(): array
    {
        return ['account_type' => $this->type, 'account_id' => $this->id];
    }

    /**
     * 同一账号的唯一字符串（advisory lock 之类需要一个键的地方用）。
     */
    public function key(): string
    {
        return $this->type.':'.$this->id;
    }
}
