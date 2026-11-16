<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\StoreMeReadingTimeRequest;
use App\Http\Resources\V3\ReadingTimeResource;
use App\Services\V3\ReadingTimeService;

/**
 * 当前登录用户的读经时长上报。读经时长按积分策略换成 AI 对话积分（游客不计）。
 *
 * 新资源，没有 v2 前身。
 */
class MeReadingTimeController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly ReadingTimeService $readingTime) {}

    /**
     * 上报一段读经时长
     *
     * 客户端在阅读器前台、且最近有滚动或翻页时计时，每 5 分钟或切后台时上报一次。
     * 服务端按墙钟时间核减（当天计入的不超过距上次上报经过的时间），单日不超过 24 小时；
     * 断网补报可带 `day`，最多 2 天前。返回这一天累计计入的秒数。
     *
     * @bodyParam seconds integer required 这段的读经秒数，1–900
     * @bodyParam day string 归属的 UTC 日（Y-m-d），缺省为今天。断网补报用
     */
    public function store(StoreMeReadingTimeRequest $request)
    {
        $data = $request->validated();

        return ReadingTimeResource::make($this->readingTime->report(
            $this->currentUser($request)['user_uid'],
            (int) $data['seconds'],
            $data['day'] ?? null,
        ));
    }
}
