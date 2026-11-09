<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexTipitakaChapterChannelRequest;
use App\Http\Resources\V3\TipitakaChapterChannelResource;
use App\Services\V3\TipitakaChapterChannelService;

/**
 * 某一章有哪些 channel 及各自的完成度——阅读页的「版本切换」用。
 *
 * 取代 `GET /v2/progress?view=chapter_channels&book=&par=`（`ProgressChapterController@index`
 * 的一个 view 分支）。v2 那份在 dashboard-v4 下线前原样保留，不要改动。
 */
class TipitakaChapterChannelController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly TipitakaChapterChannelService $chapterChannels) {}

    /**
     * 列出一章的 channel
     *
     * 按完成度从高到低。只列看得见的 channel：公开的，加上登录用户自己的、共享给他（或他所在群组）的；
     * 未登录只看得到公开的。章节不存在或没有任何 channel 时返回空列表。
     *
     * `progress` 是 0–1 的完成度；`progress_line` 是近几周每周的修改次数（从早到晚，由 `upgrade:chapter.dynamic.weekly` 每周算一次，
     * 没算过就是 null），画活跃度小图用；
     * `views` 是这一章在该 channel 下被浏览的次数。
     *
     * @unauthenticated
     *
     * @urlParam book integer required 书号。Example: 94
     * @urlParam para integer required 章节起始段落号。Example: 385
     *
     * @queryParam page integer 页码。Default: 1
     * @queryParam per_page integer 每页数量，最大 200。Default: 100
     */
    public function __invoke(IndexTipitakaChapterChannelRequest $request, int $book, int $para)
    {
        return TipitakaChapterChannelResource::collection($this->chapterChannels->list(
            $book,
            $para,
            $this->currentUserUid($request),
            $request->integer('per_page', 100),
        ));
    }
}
