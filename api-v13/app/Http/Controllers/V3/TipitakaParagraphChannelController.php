<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexTipitakaParagraphChannelRequest;
use App\Http\Resources\V3\ChannelResource;
use App\Services\V3\TipitakaChannelService;

/**
 * 某一段有内容的 channel（mobile 与 wikipali-plugins 读某段的各版本用）。
 *
 * 取代 `GET /v2/channel?view=paragraphs`（`ChannelController@index`）。与 v2 不同：只列看得见的
 * （v2 连私有 channel 也列），一次一段（v2 的 para 可逗号多段，没有调用方用）。
 * v2 那份在 dashboard-v4 下线前原样保留，不要改动。
 */
class TipitakaParagraphChannelController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly TipitakaChannelService $channels) {}

    /**
     * 列出某段有内容的 channel
     *
     * 最近更新的在前。不登录只看得到公开的。
     *
     * @unauthenticated
     *
     * @urlParam book integer required 书
     * @urlParam para integer required 段
     *
     * @queryParam page integer 页码。Default: 1
     * @queryParam per_page integer 每页数量，最大 200。Default: 100
     */
    public function __invoke(IndexTipitakaParagraphChannelRequest $request, int $book, int $para)
    {
        return ChannelResource::collection($this->channels->forParagraph(
            $book,
            $para,
            $this->currentUserUid($request),
            $request->integer('per_page', 100),
        ));
    }
}
