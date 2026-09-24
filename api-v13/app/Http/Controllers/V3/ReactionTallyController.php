<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexReactionTallyRequest;
use App\Http\Resources\V3\ReactionTallyResource;
use App\Services\V3\ReactionService;

/**
 * 某个 target 下各 type 的计数与「当前用户是否已操作」的摘要——公共只读。
 *
 * 取代 `GET /v2/like?view=count`。登录时额外富化 selected / id 两个字段，
 * 不登录也能拿到全局计数。
 *
 * 资源改名（v2 的 `like` → v3 的 `reactions`）是根目录 CLAUDE.md
 * 「已登记的改名例外」里的唯一一条，底层仍是 `likes` 表。
 */
class ReactionTallyController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly ReactionService $reactions) {}

    /**
     * 统计某个 target 下各 type 的计数
     *
     * 一次聚合查询出各 type 的 count；登录时再查一次当前用户的选择，补 selected / id。
     *
     * @unauthenticated
     *
     * @queryParam target_id string required target 的 uuid。Example: 5b4511c1-c99f-47e7-9798-97b9e5229e18
     */
    public function index(IndexReactionTallyRequest $request)
    {
        $items = $this->reactions->tally(
            $request->validated('target_id'),
            $this->currentUserUid($request),
        );

        // 非分页列表：手工给 meta，使响应形状与规格里的 {data, meta} 一致
        // FIXME: current_page / per_page / last_page 在不分页的聚合上是假值，
        // 只是为了套上生成器写死的 PaginationMeta，待定夺
        return ReactionTallyResource::collection($items)->additional(['meta' => [
            'current_page' => 1,
            'per_page' => count($items),
            'total' => count($items),
            'last_page' => 1,
        ]]);
    }
}
