<?php

namespace App\Models;

use App\Models\Concerns\OwnedByAgentAccount;
use App\Services\V3\AgentThreadService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * AI 对话的一次提问（AG-UI run）及其研究过程汇总。
 *
 * 软删除：随会话一起软删（用户删除会话，见 {@see AgentThreadService::hide()}），
 * 软删后任何接口都读不到（将来分享对话也一样），数据永久保留。用 `DB::table('agent_runs')` 查时要自己加
 * `whereNull('deleted_at')`。
 */
class AgentRun extends Model
{
    use HasFactory, OwnedByAgentAccount, SoftDeletes;

    /**
     * denied：额度不足、没有作答——照样入库，防滥用要看得到被拦下的提问。
     *
     * @var list<string>
     */
    public const STATUSES = ['ok', 'error', 'cancelled', 'denied'];

    /**
     * 研究路径（agent-poc `backend/routing.py` 的路由结果）：question 一般问题、term 术语 / 词、
     * passage 阅读页提问、chat 闲聊；还没路由就结束（额度拦截）时为 null。
     *
     * 唯一来源：Resource 的 `@return` 与控制器的 `@queryParam … Enum:` 里写的是同一组字面量（OpenAPI 据此出枚举，
     * 前端画图用），由 tests/Feature/AgentRouteEnumTest.php 守住一致；backend 的离线测试拿 OpenAPI 规格核对
     * routing.py 会产生的路由。入库时不认识的路由照样存、只记警告，不丢研究过程。
     *
     * @var list<string>
     */
    public const ROUTES = ['question', 'term', 'passage', 'chat'];

    protected $guarded = ['id'];

    protected $casts = [
        'passage' => 'array',
        'plan' => 'array',
        'citations' => 'array',
        'deep' => 'boolean',
        'improve' => 'boolean',
        'confidence' => 'float',
        'started_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<AgentThread, $this>
     */
    public function thread(): BelongsTo
    {
        return $this->belongsTo(AgentThread::class, 'agent_thread_id')->withTrashed();
    }

    /**
     * 提问的登录用户（游客为空）。uuid 列对 user_infos.userid，预加载用 UserService::eagerLoadActor() 的列白名单。
     *
     * @return BelongsTo<UserInfo, $this>
     */
    public function user(): BelongsTo
    {
        // 只对 account_type = user 有意义：游客的 account_id 是设备 id，碰巧与某个用户 uid 相同也不能当成他
        return $this->belongsTo(UserInfo::class, 'account_id', 'userid');
    }

    /**
     * @return HasMany<AgentRunStep, $this>
     */
    public function steps(): HasMany
    {
        return $this->hasMany(AgentRunStep::class)->orderBy('seq');
    }

    /**
     * 只留用户看得见的那一轮：被「重新生成」替换掉的旧轮次不算。
     *
     * @param  Builder<AgentRun>  $query
     */
    public function scopeVisible(Builder $query): void
    {
        $query->whereNotExists(function ($sub) {
            $sub->selectRaw('1')
                ->from('agent_runs as newer')
                ->whereNull('newer.deleted_at')
                ->whereColumn('newer.agent_thread_id', 'agent_runs.agent_thread_id')
                ->whereColumn('newer.replaces_run_id', 'agent_runs.run_id');
        });
    }
}
