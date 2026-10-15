<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\SummaryDiscussionRequest;
use App\Http\Resources\V3\DiscussionSummaryResource;
use App\Services\V3\DiscussionService;

/**
 * 某个对象上的话题概况。
 *
 * 取代 v2 `GET /v2/discussion` 响应里夹带的 active / close / can_create（v2 的 can_reply
 * 两个前端都没读过，不迁；能否回复改为每条话题自带的 abilities.reply）。
 */
class DiscussionSummaryController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly DiscussionService $discussions) {}

    /**
     * 话题概况
     *
     * 各状态的话题数（只数没删除的、当前用户看得见的），以及当前用户能在这个对象上
     * 发起哪些类型的话题（未登录为空数组）。
     *
     * @unauthenticated
     *
     * @queryParam target_type string required 挂靠对象的类型。Enum: sentence,wbw,article,term,task
     * @queryParam target_id string required 挂靠对象的 uuid
     * @queryParam type string 只数这种类型，不传则只数评论线程。Enum: discussion,qa,help,issue,note,commentary
     */
    public function __invoke(SummaryDiscussionRequest $request)
    {
        return DiscussionSummaryResource::make($this->discussions->summary(
            $request->validated(),
            $this->currentUserUid($request),
        ));
    }
}
