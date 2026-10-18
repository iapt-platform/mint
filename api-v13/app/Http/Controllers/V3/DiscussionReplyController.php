<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexDiscussionReplyRequest;
use App\Http\Requests\V3\StoreDiscussionReplyRequest;
use App\Http\Resources\V3\DiscussionResource;
use App\Models\Discussion;
use App\Services\V3\AgentGrantService;
use App\Services\V3\DiscussionService;

/**
 * 话题下的回复。线程只有两层：回复不能再被回复。
 *
 * 取代 `GET /v2/discussion?view=answer` 与带 `parent` 的 `POST /v2/discussion`。
 */
class DiscussionReplyController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(
        private readonly DiscussionService $discussions,
        private readonly AgentGrantService $grants,
    ) {}

    /**
     * 列出话题的回复
     *
     * 按时间正序。删除了的话题（墓碑）下的回复照样可读。
     *
     * @unauthenticated
     *
     * @urlParam discussion string required 话题的 uuid
     *
     * @queryParam page integer 页码。Default: 1
     * @queryParam per_page integer 每页数量，最大 200。Default: 50
     */
    public function index(IndexDiscussionReplyRequest $request, Discussion $discussion)
    {
        return DiscussionResource::collection($this->discussions->listReplies(
            $discussion,
            $this->currentUserUid($request),
            $request->integer('per_page', 50),
        ));
    }

    /**
     * 回复话题
     *
     * 类型与挂靠对象继承话题。已关闭的话题不能回复（409），对一条回复再回复 422。
     * AI 代写同 `POST /v3/discussions`（`X-Agent-Grant` 请求头）。
     * 文章上的 qa 只有文章的编辑者能回答。默认发通知，`notify=false` 关闭。
     *
     * @responseStatus 201 回复成功
     * @responseStatus 403 当前用户不能回复这个话题
     * @responseStatus 409 话题已关闭
     *
     * @urlParam discussion string required 话题的 uuid
     *
     * @bodyParam content string required 正文（markdown）
     * @bodyParam notify boolean 是否发通知。Default: true
     */
    public function store(StoreDiscussionReplyRequest $request, Discussion $discussion)
    {
        return DiscussionResource::make($this->discussions->createReply(
            $discussion,
            $this->grants->actor(
                $this->currentUser($request)['user_uid'],
                $request->header(AgentGrantService::HEADER),
            ),
            $request->validated(),
        ));
    }
}
