<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexTipitakaChannelRequest;
use App\Http\Resources\V3\TipitakaChannelResource;
use App\Services\V3\TipitakaChannelService;

/**
 * 有译文的公开 channel 目录（mobile 发现页「译本合集」）。
 *
 * 取代 `GET /v2/progress?view=channel`（`ProgressChapterController@index`）。v2 那份在 dashboard-v4
 * 下线前原样保留，不要改动。
 */
class TipitakaChannelController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly TipitakaChannelService $channels) {}

    /**
     * 列出有译文的公开 channel
     *
     * 只列公开的、有完成度过门槛的章节的 channel，按章节数降序，每条带 `chapter_count`。
     *
     * @unauthenticated
     *
     * @queryParam type string channel 类型。Enum: translation,nissaya,original,wbw,commentary,similar
     * @queryParam lang string 译文语族（progress_chapters.lang，zh 涵盖简繁）。Example: zh
     * @queryParam min_progress number 章节完成度门槛 0–1。Default: 0.8
     * @queryParam page integer 页码。Default: 1
     * @queryParam per_page integer 每页数量，最大 200。Default: 20
     */
    public function __invoke(IndexTipitakaChannelRequest $request)
    {
        return TipitakaChannelResource::collection($this->channels->withChapters(
            $request->safe()->only(['type', 'lang', 'min_progress']),
            $this->currentUserUid($request),
            $request->integer('per_page', 20),
        ));
    }
}
