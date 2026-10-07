<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexReactionRequest;
use App\Http\Resources\V3\ReactionResource;
use App\Services\V3\ReactionService;

/**
 * 某个 target 下的 reactions（点赞/收藏/书签/关注/下载记录）——公共只读。
 *
 * 取代 `GET /v2/like?view=target`。与 `/v3/me/reactions`（当前用户自己的 reactions）
 * 分属两个控制器：那边全线要求登录，这边全线公开。
 *
 * 资源改名（v2 的 `like` → v3 的 `reactions`）是根目录 CLAUDE.md
 * 「已登记的改名例外」里的唯一一条，底层仍是 `likes` 表。
 */
class ReactionController extends Controller
{
    public function __construct(private readonly ReactionService $reactions) {}

    /**
     * 列出某个 target 下的 reactions
     *
     * 按 target 聚合的公共列表，不要求登录。分页由 Laravel paginator 提供。
     *
     * @unauthenticated
     *
     * @queryParam target_id string required target 的 uuid。Example: 5b4511c1-c99f-47e7-9798-97b9e5229e18
     * @queryParam type string 操作类型。Enum: like,dislike,favorite,watch,bookmark,download
     * @queryParam page integer 页码。Default: 1
     * @queryParam per_page integer 每页数量，最大 200。Default: 15
     */
    public function index(IndexReactionRequest $request)
    {
        $data = $request->validated();

        return ReactionResource::collection($this->reactions->listForTarget(
            $data['target_id'],
            $data['type'] ?? null,
            $request->integer('per_page', 15),
        ));
    }
}
