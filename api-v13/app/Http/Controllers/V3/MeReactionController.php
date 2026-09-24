<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexMeReactionRequest;
use App\Http\Requests\V3\StoreMeReactionRequest;
use App\Http\Resources\V3\ReactionResource;
use App\Models\Reaction;
use App\Services\V3\ReactionService;
use Illuminate\Http\Request;

/**
 * 当前登录用户自己的 reactions（点赞/收藏/书签/关注/下载记录）。
 *
 * 与 `/v3/reactions`（公共只读）分属两个控制器：这个控制器全线要求登录，
 * user_id 一律取当前登录用户，不接受客户端指定——替别人加关注是 target 的
 * 关注者管理，暂留在 v2，不属于这里。
 *
 * 取代 `POST /v2/like` 与 `DELETE /v2/like/{like}`。资源改名（v2 的 `like` →
 * v3 的 `reactions`）是根目录 CLAUDE.md「已登记的改名例外」里的唯一一条，
 * 底层仍是 `likes` 表。
 */
class MeReactionController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly ReactionService $reactions) {}

    /**
     * 列出我自己的 reactions
     *
     * @queryParam type string 操作类型。Enum: like,dislike,favorite,watch,bookmark,download
     * @queryParam target_type string target 的类型（task 任务、collection 文集、progress_chapter 书·章节、article 文章、terms 术语）。Enum: task,collection,progress_chapter,article,terms
     * @queryParam page integer 页码。Default: 1
     * @queryParam per_page integer 每页数量，最大 200。Default: 15
     */
    public function index(IndexMeReactionRequest $request)
    {
        $user = $this->currentUser($request);

        return ReactionResource::collection($this->reactions->listForUser(
            $user['user_uid'],
            $request->validated(),
            $request->integer('per_page', 15),
        ));
    }

    /**
     * 添加一条我自己的 reaction（幂等）
     *
     * 唯一键是 (type, target_id, user_id)：重复提交不会产生第二条记录，返回同一条。
     * 首次创建返回 201，命中已存在的记录返回 200。
     *
     * 201 不是手写的状态码：Laravel 的 ResourceResponse::calculateStatus() 看模型的
     * wasRecentlyCreated，新建时自动给 201。这里不要改成固定 200——幂等端点区分
     * 「真的建了」与「早就有了」对调用方有用。
     *
     * 返回的是 reaction 资源本身（调用方需要 id 才能后续删除），**不含计数**。
     * 计数是共享聚合值，归 `GET /v3/reactions/tally` 管；前端用乐观更新 ±1，
     * 下次读 tally 时校正。见 v3-resource skill 硬规范第 2 条。
     *
     * @responseStatus 201 首次创建。响应体与 200 同形，区别只在这一条是本次新建的
     *
     * @bodyParam type string required 操作类型。Enum: like,dislike,favorite,watch,bookmark,download
     * @bodyParam target_id string required target 的 uuid
     * @bodyParam target_type string required target 的类型（task 任务、collection 文集、progress_chapter 书·章节、article 文章、terms 术语）。Enum: task,collection,progress_chapter,article,terms
     * @bodyParam context string 附加上下文，最长 128 字符
     */
    public function store(StoreMeReactionRequest $request)
    {
        $user = $this->currentUser($request);

        return new ReactionResource(
            $this->reactions->add($user['user_uid'], $request->validated())
        );
    }

    /**
     * 删除我自己的 reaction
     *
     * 只能删自己那条（user_id 与当前用户不符返回 403）。
     *
     * 成功返回 **204 空体**：硬删就是删了，没有资源可回，计数也不在这里给
     * （见 v3-resource skill 硬规范第 2 条）。
     *
     * @urlParam reaction string required reaction 的 uuid
     *
     * @responseStatus 204 删除成功，无响应体
     * @responseStatus 403 只能删除自己的 reaction
     */
    public function destroy(Request $request, Reaction $reaction)
    {
        $this->reactions->remove($reaction, $this->currentUser($request)['user_uid']);

        return response()->noContent();
    }
}
