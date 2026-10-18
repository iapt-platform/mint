<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\BatchCountDiscussionRequest;
use App\Http\Resources\V3\DiscussionCountResource;
use App\Services\V3\DiscussionService;

/**
 * 一批对象的讨论角标。
 *
 * 取代 `POST /v2/discussion-count` 的讨论部分（`discussions`）；那个端点同时返回标签，
 * 标签部分还没迁，v6 暂时仍调它取 `tags`。v2 那份在 dashboard-v4 下线前原样保留。
 */
class DiscussionBatchCountController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly DiscussionService $discussions) {}

    /**
     * 批量取讨论角标
     *
     * 每个对象返回未关闭的评论线程数（total）与其中当前用户发起的数目（mine，未登录为 0），
     * 按请求顺序、一个不少（没有讨论的为 0）。只数评论线程（discussion / qa / help / issue），
     * 批注与已删除的不算；可见性与列表相同：未登录不数协作类话题，basic 角色在无权管理的
     * 对象上只数自己发起的协作类话题。最多 500 个对象。
     *
     * 与 v2 的区别：入参是对象 uid 而不是句子坐标；只给计数不给明细；v2 不分类型、不管可见性。
     *
     * @unauthenticated
     *
     * @bodyParam targets array required 讨论对象列表，每项 {type, id}，最多 500 个
     * @bodyParam targets.*.type string required 对象类型。Enum: sentence,wbw
     * @bodyParam targets.*.id string required 对象 uid
     */
    public function __invoke(BatchCountDiscussionRequest $request)
    {
        return DiscussionCountResource::collection($this->discussions->batchCount(
            $request->validated('targets'),
            $this->currentUserUid($request),
        ));
    }
}
