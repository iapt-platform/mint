<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\LookupTermRequest;
use App\Http\Requests\V3\ShowTermRequest;
use App\Http\Requests\V3\UpdateTermRequest;
use App\Http\Resources\V3\TermResource;
use App\Models\DhammaTerm;
use App\Services\V3\AgentGrantService;
use App\Services\V3\TermService;
use Illuminate\Http\Request;

/**
 * 术语 / 百科词条：按词查、单条、改、删。
 *
 * 取代 `GET /v2/terms?view=word`、`GET /v2/terms/{id}`、`PUT /v2/terms/{id}`、`DELETE /v2/terms/{id}`
 * （`DhammaTermController`）。v2 那份在 dashboard-v4 下线前原样保留，不要改动。
 * 列表与新建在 {@see ChannelTermController}、{@see StudioTermController}。
 *
 * AI 模型可凭人类授权（请求头 `X-Agent-Grant`）代写 channel 里的术语：editor 记授权人、co_editor 记模型。
 */
class TermController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(
        private readonly TermService $terms,
        private readonly AgentGrantService $grants,
    ) {}

    /**
     * 按词查术语
     *
     * 这些词在你看得见的 channel 与 studio 级术语里的词条（编辑时看同词的其它义项、社区译法）。
     *
     * @unauthenticated
     *
     * @queryParam word string required 巴利词，逗号分隔，最多 50 个
     * @queryParam tag string 义项；传空串只要没有义项的
     * @queryParam channel string 只要这个 channel 的
     * @queryParam language string 只要这个语言的
     * @queryParam with_body boolean 每条都带渲染后的正文 `body`
     * @queryParam page integer 页码
     * @queryParam per_page integer 每页条数，最大 200。Default: 20
     */
    public function index(LookupTermRequest $request)
    {
        return TermResource::collection($this->terms->lookup(
            ['with_body' => $request->boolean('with_body')] + $request->validated(),
            $this->currentUserUid($request),
            $request->integer('per_page', 20),
        ));
    }

    /**
     * 术语详情
     *
     * 带渲染后的「摘要 + 正文」（`body`）。channel 里的术语跟 channel 的可见性，studio 级的谁都能看。
     * 响应带 `ETag` 头（与 `etag` 字段相同），修改时放进 `If-Match`。
     *
     * @unauthenticated
     *
     * @urlParam term string required 术语 id（guid）
     *
     * @queryParam channels string 正文按这些 channel 解析（句子、术语），逗号分隔，最多 20 个；只认你看得见的，不给就用术语所在 channel
     * @queryParam mode string 正文模式。Enum: read,edit Default: read
     * @queryParam format string 正文格式：tree 给节点树 `body`，html 给 HTML 字符串 `html`（WebView 显示用）。Enum: tree,html Default: tree
     *
     * @responseStatus 403 所在 channel 看不见
     */
    public function show(ShowTermRequest $request, DhammaTerm $term)
    {
        return TermResource::make($this->terms->show($term, $this->currentUserUid($request), $request->render()));
    }

    /**
     * 改术语
     *
     * 只改传了的字段；word / tag 不能改（要改就新建一条，把旧的 redirect 过去）。必须带请求头
     * `If-Match`（取自详情的 `etag` / `ETag`）：不带 428，这条在你编辑期间被改过 412（响应里给出最新的 `etag`）。
     * 分类从 note 的 `{{category|…}}` 重新抽取。channel 里的术语跟 channel 的写权限，studio 级的要团队 editor 以上。
     *
     * @urlParam term string required 术语 id（guid）
     *
     * @bodyParam meaning string 释义
     * @bodyParam other_meaning string|null 其它释义
     * @bodyParam note string|null 正文
     * @bodyParam summary string|null 摘要，格式与 note 相同
     * @bodyParam quality string|null 质量。Enum: featured,standard,draft,pending
     * @bodyParam content_type string 正文方言。Enum: markdown,markdown-e2
     * @bodyParam redirect string|null 重定向到 word 或 word@tag
     * @bodyParam language string 语言（只有 studio 级术语能改）
     *
     * @responseStatus 403 不能修改
     * @responseStatus 412 这条在你编辑期间被改过
     * @responseStatus 428 没带 If-Match
     */
    public function update(UpdateTermRequest $request, DhammaTerm $term)
    {
        return TermResource::make($this->terms->update(
            $term,
            $this->grants->actor(
                $this->currentUser($request)['user_uid'],
                $request->header(AgentGrantService::HEADER),
            ),
            $request->validated(),
            $request->header('If-Match'),
        ));
    }

    /**
     * 删术语
     *
     * 软删除，返回被删的术语。拥有者本人或团队 owner 能删；AI 模型不能删。
     *
     * @urlParam term string required 术语 id（guid）
     *
     * @responseStatus 403 不能删除
     */
    public function destroy(Request $request, DhammaTerm $term)
    {
        return TermResource::make($this->terms->delete($term, $this->currentUser($request)['user_uid']));
    }
}
