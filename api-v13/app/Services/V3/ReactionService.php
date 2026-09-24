<?php

namespace App\Services\V3;

use App\Models\Reaction;
use App\Services\UserService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * reactions（点赞/收藏/书签/关注/下载记录）的业务逻辑。
 *
 * 底层是历史表 `likes`，v2 的 `LikeController` 另有一套实现，两边不共用——
 * 见 CLAUDE.md 铁律 2：契约相关的逻辑 v3 重写，不为共享去改 v2。
 */
class ReactionService
{
    /**
     * 某个 target 下的公共列表。
     *
     * @return LengthAwarePaginator<int, Reaction>
     */
    public function listForTarget(string $targetId, ?string $type, int $perPage): LengthAwarePaginator
    {
        return $this->withActor()
            ->where('target_id', $targetId)
            ->when($type, fn ($q, $value) => $q->where('type', $value))
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * 某个用户自己的列表。
     *
     * @param  array{type?: string, target_type?: string}  $filters
     * @return LengthAwarePaginator<int, Reaction>
     */
    public function listForUser(string $userUid, array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->withActor()
            ->where('user_id', $userUid)
            ->when($filters['type'] ?? null, fn ($q, $value) => $q->where('type', $value))
            ->when($filters['target_type'] ?? null, fn ($q, $value) => $q->where('target_type', $value))
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * 添加一条 reaction，幂等。
     *
     * 唯一键是 (type, target_id, user_id)：重复提交命中同一条，不新建。
     * 返回的模型上 `wasRecentlyCreated` 决定响应是 201 还是 200，
     * 由 Laravel 的 ResourceResponse::calculateStatus() 读取。
     *
     * @param  array{type: string, target_id: string, target_type: string, context?: string|null}  $data
     */
    public function add(string $userUid, array $data): Reaction
    {
        $reaction = Reaction::firstOrNew([
            'type' => $data['type'],
            'target_id' => $data['target_id'],
            'user_id' => $userUid,
        ]);

        // id 不在 $fillable 里，且主键是 uuid（非自增），必须显式赋值，
        // 否则 save() 后拿不到生成的 id，前端无法据此删除。
        if (! $reaction->exists) {
            $reaction->id = (string) Str::uuid();
            $reaction->target_type = $data['target_type'];
            $reaction->context = $data['context'] ?? null;
        }
        $reaction->save();

        return $reaction;
    }

    /**
     * 删除一条 reaction。只能删自己那条。
     *
     * 抛 AuthorizationException 而不是 abort(403)：权限判定是业务规则，属于这一层；
     * 转成 HTTP 响应由 bootstrap/app.php 的处理器做（prepareException 会把它
     * 转成 403，message 进 Problem 的 detail）。
     *
     * @throws AuthorizationException 不是本人
     */
    public function remove(Reaction $reaction, string $userUid): Reaction
    {
        if ($reaction->user_id !== $userUid) {
            throw new AuthorizationException(__('site.forbidden'));
        }
        $reaction->delete();

        return $reaction;
    }

    /**
     * 某个 target 下各 type 的计数；给了 userUid 就额外标出他选了哪些。
     *
     * @return Collection<int, array{type: string, count: int, selected: bool, id: string|null}>
     */
    public function tally(string $targetId, ?string $userUid): Collection
    {
        $rows = Reaction::query()
            ->where('target_id', $targetId)
            ->groupBy('type')
            ->selectRaw('type, count(*) as count')
            ->get();

        $mine = $userUid === null ? [] : Reaction::query()
            ->where('target_id', $targetId)
            ->where('user_id', $userUid)
            ->pluck('id', 'type')
            ->all();

        return $rows->map(fn ($row) => [
            'type' => $row->type,
            'count' => (int) $row->count,
            'selected' => array_key_exists($row->type, $mine),
            'id' => $mine[$row->type] ?? null,
        ]);
    }

    /**
     * 带操作者预加载的基础查询。
     *
     * 列白名单在 UserService 里，别在调用处手抄——抄漏匹配列（userid / uid）
     * 会让关系静默对不上、整列变 null，不报错。
     *
     * @return Builder<Reaction>
     */
    private function withActor()
    {
        return Reaction::query()->with(UserService::eagerLoadActor());
    }
}
