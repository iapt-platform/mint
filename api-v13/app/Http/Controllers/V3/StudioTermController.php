<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexTermRequest;
use App\Http\Requests\V3\StoreTermRequest;
use App\Http\Resources\V3\TermResource;
use App\Services\V3\AgentGrantService;
use App\Services\V3\TermExchangeService;
use App\Services\V3\TermService;

/**
 * 某个 studio（本人或团队）的术语：列表与新建 studio 级术语。
 *
 * 取代 `GET /v2/terms?view=studio` 与不带 channel 的 `POST /v2/terms`（`DhammaTermController`），v2 原样保留。
 */
class StudioTermController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(
        private readonly TermService $terms,
        private readonly AgentGrantService $grants,
        private readonly TermExchangeService $exchange,
    ) {}

    /**
     * studio 的术语列表
     *
     * studio 名下的全部术语（它的 channel 里的与 studio 级的）。本人或团队成员能看。请求头 `Accept` 是
     * `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet` 时导出成 xlsx（列与说明同 channel 的术语列表），
     * 取代 v2 `terms-export?view=studio`。
     *
     * @urlParam studio string required 用户或团队的 uuid
     *
     * @queryParam search string 按词、释义搜索
     * @queryParam quality string 质量；pending 含未评级的。Enum: featured,standard,draft,pending
     * @queryParam category string 百科分类
     * @queryParam general boolean 只要 studio 级术语（不属于任何 channel）
     * @queryParam order string 排序字段。Enum: updated_at,created_at,word Default: updated_at
     * @queryParam dir string 排序方向。Enum: asc,desc
     * @queryParam page integer 页码
     * @queryParam per_page integer 每页条数，最大 200。Default: 20
     *
     * @responseStatus 403 不是本人，也不是团队成员
     */
    public function index(IndexTermRequest $request, string $studio)
    {
        if (in_array(TermExchangeService::MIME, $request->getAcceptableContentTypes(), true)) {
            return response($this->exchange->export($this->terms->filtered(
                $this->terms->studioQuery($studio, ['general' => $request->boolean('general')] + $request->validated(), $this->currentUser($request)['user_uid']),
                $request->validated(),
            )), 200, ['Content-Type' => TermExchangeService::MIME, 'Content-Disposition' => 'attachment; filename="terms.xlsx"']);
        }

        return TermResource::collection($this->terms->listInStudio(
            $studio,
            ['general' => $request->boolean('general')] + $request->validated(),
            $this->currentUser($request)['user_uid'],
            $request->integer('per_page', 20),
        ));
    }

    /**
     * 新建 studio 级术语
     *
     * 不属于任何 channel，owner 名下所有 channel 共用。本人或团队 owner / manager 能建；AI 模型不能建。
     * 同一 studio、同一语言里 word@tag 唯一（409）。
     *
     * @urlParam studio string required 用户或团队的 uuid
     *
     * @bodyParam word string required 巴利词，建立后不能改
     * @bodyParam tag string|null 义项（同词多义时区分），不能含 @，建立后不能改
     * @bodyParam meaning string required 释义
     * @bodyParam language string required 语言
     * @bodyParam other_meaning string|null 其它释义
     * @bodyParam note string|null 正文
     * @bodyParam summary string|null 摘要，格式与 note 相同
     * @bodyParam quality string|null 质量。Enum: featured,standard,draft,pending
     * @bodyParam content_type string 正文方言。Enum: markdown,markdown-e2 Default: markdown-e2
     * @bodyParam redirect string|null 重定向到 word 或 word@tag
     *
     * @responseStatus 201 已新建
     * @responseStatus 403 不能在这个 studio 新建
     * @responseStatus 409 已有这个 word@tag
     */
    public function store(StoreTermRequest $request, string $studio)
    {
        return TermResource::make($this->terms->createInStudio(
            $studio,
            $this->grants->actor(
                $this->currentUser($request)['user_uid'],
                $request->header(AgentGrantService::HEADER),
            ),
            $request->validated(),
        ))->response()->setStatusCode(201);
    }
}
