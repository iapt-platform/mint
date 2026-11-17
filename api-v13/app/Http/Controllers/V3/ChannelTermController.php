<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexTermRequest;
use App\Http\Requests\V3\StoreTermRequest;
use App\Http\Resources\V3\TermResource;
use App\Models\Channel;
use App\Services\V3\AgentGrantService;
use App\Services\V3\TermExchangeService;
use App\Services\V3\TermService;

/**
 * 某个 channel 的术语：列表与新建。
 *
 * 取代 `GET /v2/terms?view=channel` 与带 channel 的 `POST /v2/terms`（`DhammaTermController`），v2 原样保留。
 */
class ChannelTermController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(
        private readonly TermService $terms,
        private readonly AgentGrantService $grants,
        private readonly TermExchangeService $exchange,
    ) {}

    /**
     * channel 的术语列表
     *
     * 能读 channel 的人都能看。请求头 `Accept` 是
     * `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet` 时导出成 xlsx：整表（过滤同列表、不分页），
     * 列为 id / word / tag / meaning / other_meaning / summary / note / quality / content_type / redirect / language /
     * channel_id / etag，可改后用导入接口传回（取代 v2 `terms-export`）。
     *
     * @unauthenticated
     *
     * @urlParam channel string required channel uid
     *
     * @queryParam search string 按词、释义搜索
     * @queryParam quality string 质量；pending 含未评级的。Enum: featured,standard,draft,pending
     * @queryParam category string 百科分类
     * @queryParam order string 排序字段。Enum: updated_at,created_at,word Default: updated_at
     * @queryParam dir string 排序方向。Enum: asc,desc
     * @queryParam page integer 页码
     * @queryParam per_page integer 每页条数，最大 200。Default: 20
     *
     * @responseStatus 403 私有 channel，且看不见
     */
    public function index(IndexTermRequest $request, Channel $channel)
    {
        if (in_array(TermExchangeService::MIME, $request->getAcceptableContentTypes(), true)) {
            return response($this->exchange->export($this->terms->filtered(
                $this->terms->channelQuery($channel, $this->currentUserUid($request)),
                $request->validated(),
            )), 200, ['Content-Type' => TermExchangeService::MIME, 'Content-Disposition' => 'attachment; filename="terms.xlsx"']);
        }

        return TermResource::collection($this->terms->listInChannel(
            $channel,
            $request->validated(),
            $this->currentUserUid($request),
            $request->integer('per_page', 20),
        ));
    }

    /**
     * 在 channel 里新建术语
     *
     * 能写 channel 内容的人（含团队 editor）。owner 与语言跟 channel。同一 channel 里 word@tag 唯一（409）。
     * 分类写在 note 的 `{{category|…}}` 里。AI 模型凭 `X-Agent-Grant` 代写。
     *
     * @urlParam channel string required channel uid
     *
     * @bodyParam word string required 巴利词，建立后不能改
     * @bodyParam tag string|null 义项（同词多义时区分），不能含 @，建立后不能改
     * @bodyParam meaning string required 释义
     * @bodyParam other_meaning string|null 其它释义
     * @bodyParam note string|null 正文
     * @bodyParam summary string|null 摘要，格式与 note 相同
     * @bodyParam quality string|null 质量。Enum: featured,standard,draft,pending
     * @bodyParam content_type string 正文方言。Enum: markdown,markdown-e2 Default: markdown-e2
     * @bodyParam redirect string|null 重定向到 word 或 word@tag
     *
     * @responseStatus 201 已新建
     * @responseStatus 403 不能写这个 channel
     * @responseStatus 409 已有这个 word@tag
     */
    public function store(StoreTermRequest $request, Channel $channel)
    {
        return TermResource::make($this->terms->createInChannel(
            $channel,
            $this->grants->actor(
                $this->currentUser($request)['user_uid'],
                $request->header(AgentGrantService::HEADER),
            ),
            $request->validated(),
        ))->response()->setStatusCode(201);
    }
}
