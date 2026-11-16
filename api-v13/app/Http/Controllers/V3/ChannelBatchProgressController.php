<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\ChannelBatchProgressRequest;
use App\Http\Resources\V3\ChannelProgressResource;
use App\Services\V3\ChannelProgressService;

/**
 * 一批句子在各 channel 的完成度。
 *
 * 取代 `POST /v2/channel-progress`（`ChannelController@progress`）：v2 的 `owner=all|my` 改成可选的
 * `studio`（本人或团队的 uuid，团队空间传团队）。v2 那份在 dashboard-v4 下线前原样保留，不要改动。
 */
class ChannelBatchProgressController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly ChannelProgressService $progress) {}

    /**
     * 查一批句子在各 channel 的完成度
     *
     * 列出这些句子上有译文且看得见的 channel，加上我能写的 channel（没译文也列）。
     * 给了 `studio` 只列该 studio 名下的。不登录只看得到公开的。
     *
     * @unauthenticated
     *
     * @bodyParam sentences string[] required 句子 id 列表，`book-para-wordStart-wordEnd`，最多 2000 个
     * @bodyParam studio string 只看这个 studio（用户或团队 uuid）名下的
     *
     * @responseStatus 403 给了 studio，但不能查看它
     */
    public function __invoke(ChannelBatchProgressRequest $request)
    {
        return ChannelProgressResource::collection($this->progress->forSentences(
            $request->validated('sentences'),
            $this->currentUserUid($request),
            $request->validated('studio'),
        ));
    }
}
