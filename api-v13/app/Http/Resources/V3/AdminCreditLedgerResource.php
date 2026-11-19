<?php

namespace App\Http\Resources\V3;

use App\Http\Resources\Concerns\ResolvesActor;
use App\Models\CreditLedgerEntry;
use Illuminate\Http\Request;

/**
 * 积分流水的一条（管理员视角）。不给自增 id：流水由 (账号, source, ref, kind, bucket) 唯一确定。
 * **本类不查库**：user、operator 由 Service 预加载。
 *
 * ref 的含义随 source 变：agent_run 是提问的 run_id（可在 AI 对话统计里打开研究过程），
 * reading / editing 是结算的日期，admin 是调整单号。
 *
 * @mixin CreditLedgerEntry
 */
class AdminCreditLedgerResource extends BaseResource
{
    use ResolvesActor;

    /**
     * @param  Request  $request
     * @return array{
     *     account_type: 'user'|'guest',
     *     account_id: string,
     *     user: array{id: string, nickName: string, userName: string, realName: string, sn: int, avatar: string|null, roles: array|null}|null,
     *     delta: int,
     *     kind: 'earn'|'spend'|'refund'|'adjust',
     *     bucket: 'free'|'earned',
     *     source: 'reading'|'editing'|'agent_run'|'admin',
     *     ref: string,
     *     day: string,
     *     policy_version: int,
     *     client_ip: string|null,
     *     note: string|null,
     *     operator: array{id: string, nickName: string, userName: string, realName: string, sn: int, avatar: string|null, roles: array|null}|null,
     *     created_at: string,
     *     updated_at: string
     * }
     */
    public function toArray($request): array
    {
        return [
            'account_type' => $this->account_type,
            'account_id' => $this->account_id,
            'user' => $this->account_type === 'user' && $this->user ? $this->actor($this->user) : null,
            'delta' => $this->delta,
            'kind' => $this->kind,
            'bucket' => $this->bucket,
            'source' => $this->source,
            'ref' => $this->ref,
            'day' => $this->day->toDateString(),
            'policy_version' => (int) $this->policy_version,
            'client_ip' => $this->client_ip,
            'note' => $this->note,
            'operator' => $this->operator ? $this->actor($this->operator) : null,
            'created_at' => $this->created_at->toIso8601ZuluString(),
            'updated_at' => $this->updated_at->toIso8601ZuluString(),
        ];
    }
}
