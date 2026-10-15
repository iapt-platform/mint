<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexDiscussionRequest;
use App\Http\Requests\V3\StoreDiscussionRequest;
use App\Http\Requests\V3\UpdateDiscussionRequest;
use App\Http\Resources\V3\DiscussionResource;
use App\Models\Discussion;
use App\Services\V3\AgentGrantService;
use App\Services\V3\DiscussionService;
use Illuminate\Http\Request;

/**
 * 评论线程：挂在句子、逐词、文章、术语、任务上的话题与回复；以及挂在句子上的批注
 * （note 边注、commentary 义注 / 复注对应——巴利注释书，不是普通评论）。
 *
 * 取代 `GET/POST /v2/discussion`（view=question / res_id / question-by-topic）、
 * `GET/PUT/DELETE /v2/discussion/{discussion}`。v2 那份在 dashboard-v4 下线前原样保留。
 * 回复见 {@see DiscussionReplyController}（取代 view=answer 与带 parent 的 POST）。
 *
 * 底层仍是 `discussions` 表。批注会注入阅读页，规则与评论不同，见各方法说明。
 *
 * 写操作（新建、修改、删除，以及回复）支持 AI 代写：模型用模型 token 登录，并带请求头
 * `X-Agent-Grant`（人类经 `POST /v3/agent-grants` 签发）。此时 editor 记授权人、
 * co_editor 记模型，权限按授权人判断，且只能写授权覆盖的 channel / book 里的句子上的内容。
 * 模型不带授权写入一律 403。
 */
class DiscussionController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(
        private readonly DiscussionService $discussions,
        private readonly AgentGrantService $grants,
    ) {}

    /**
     * 列出某个对象上的话题
     *
     * 只列话题（不含回复），删除了但还有回复的话题以墓碑出现（deleted_at 有值、内容为空）。
     * 未登录看不到协作类话题（discussion / issue）；basic 角色在自己无权管理的对象上只看得到
     * 自己发起的协作类话题。各状态的数量与「能发起哪些类型」见 `GET /v3/discussions/summary`。
     * 不传 type 时只列评论线程（discussion / qa / help / issue），批注要显式 type=note / commentary。
     *
     * @unauthenticated
     *
     * @queryParam target_type string required 挂靠对象的类型。Enum: sentence,wbw,article,term,task
     * @queryParam target_id string required 挂靠对象的 uuid
     * @queryParam type string 类型，不传则只列评论线程。Enum: discussion,qa,help,issue,note,commentary
     * @queryParam status string 话题状态，不传则两种都要。Enum: active,close
     * @queryParam severity string 严重程度，逗号分隔多选。Example: error,warning
     * @queryParam sort string 排序，前缀 - 表示倒序。Enum: created_at,-created_at,updated_at,-updated_at Default: -created_at
     * @queryParam page integer 页码。Default: 1
     * @queryParam per_page integer 每页数量，最大 200。Default: 15
     */
    public function index(IndexDiscussionRequest $request)
    {
        return DiscussionResource::collection($this->discussions->listTopics(
            $request->validated(),
            $this->currentUserUid($request),
            $request->integer('per_page', 15),
        ));
    }

    /**
     * 发起话题或新建批注
     *
     * discussion / issue 登录即可发；qa / help 只能发在文章上，且只有文章的编辑者能发。
     * severity 的取值随 type：issue 为 error / warning / suggestion，help 为 urgent / normal，
     * 其它类型不收。默认会通知对象的作者（站内信 + webhook），`notify=false` 关闭。
     *
     * 批注（note / commentary）只能加在句子上，且只有该句所在 channel 的编辑者能加；
     * 标题可省、正文必填，commentary 的正文必须是义注句子模板 `{{book-para-start-end}}`
     * （可多句并列）；可带锚点 pos_* / quote_*（评论线程传了 422）；不发通知。
     *
     * @responseStatus 201 创建成功
     * @responseStatus 403 当前用户不能在这个对象上发起这种类型的话题
     *
     * @bodyParam target_type string required 挂靠对象的类型。Enum: sentence,wbw,article,term,task
     * @bodyParam target_id string required 挂靠对象的 uuid
     * @bodyParam type string 类型。Enum: discussion,qa,help,issue,note,commentary Default: discussion
     * @bodyParam title string 标题，最长 256 字符；评论线程必填，批注可省
     * @bodyParam content string 正文（markdown）；批注必填
     * @bodyParam severity string 严重程度。Enum: error,warning,suggestion,urgent,normal
     * @bodyParam pos_start integer 批注锚点在句子文本中的起始偏移（只限批注）
     * @bodyParam pos_end integer 批注锚点的结束偏移，批注插在这里（只限批注）
     * @bodyParam quote_exact string 锚定的原文片段（只限批注）
     * @bodyParam quote_prefix string 锚定片段前的上下文，用于重新定位（只限批注）
     * @bodyParam quote_suffix string 锚定片段后的上下文，用于重新定位（只限批注）
     * @bodyParam tpl_id string 话题模板（文章）的 uuid
     * @bodyParam notify boolean 是否发通知（批注不发）。Default: true
     */
    public function store(StoreDiscussionRequest $request)
    {
        return DiscussionResource::make($this->discussions->createTopic(
            $this->grants->actor(
                $this->currentUser($request)['user_uid'],
                $request->header(AgentGrantService::HEADER),
            ),
            $request->validated(),
        ));
    }

    /**
     * 获取单条话题或回复
     *
     * 删除了但还有回复的话题返回墓碑；删除了的回复、删除了且没有回复的话题返回 404。
     * 当前用户看不见的话题同样 404。
     *
     * @unauthenticated
     *
     * @urlParam discussion string required 话题或回复的 uuid
     */
    public function show(Request $request, Discussion $discussion)
    {
        return DiscussionResource::make(
            $this->discussions->show($discussion, $this->currentUserUid($request))
        );
    }

    /**
     * 修改话题或回复（局部更新）
     *
     * 只改传了的字段。title / status / type / severity 只对话题有效，回复传了 422。
     * 作者或对象的管理者（句子、逐词所在 channel 的编辑者，文章的编辑者）可改；
     * 改 type 时回复跟着改，新类型不收原来的 severity 时 severity 清空。
     * 批注：没有 status，type 只能在 note / commentary 之间改（不能与评论线程互转），
     * 正文不能清空，可改锚点。
     * 改了内容（标题、正文、锚点）时 co_editor 跟着这次写入走：人亲自改则清空，AI 代改则换成该模型；
     * 只改 status / type / severity 不动 co_editor。
     *
     * @responseStatus 403 没有修改权限
     *
     * @urlParam discussion string required 话题或回复的 uuid
     *
     * @bodyParam title string 标题（只限话题）
     * @bodyParam content string 正文（markdown）
     * @bodyParam status string 状态（只限话题）。Enum: active,close
     * @bodyParam type string 类型（只限话题；评论线程与批注不能互转）。Enum: discussion,qa,help,issue,note,commentary
     * @bodyParam severity string 严重程度（只限话题），null 表示清空。Enum: error,warning,suggestion,urgent,normal
     * @bodyParam pos_start integer 锚点起始偏移（只限批注）
     * @bodyParam pos_end integer 锚点结束偏移（只限批注）
     * @bodyParam quote_exact string 锚定的原文片段（只限批注）
     * @bodyParam quote_prefix string 锚定片段前的上下文（只限批注）
     * @bodyParam quote_suffix string 锚定片段后的上下文（只限批注）
     */
    public function update(UpdateDiscussionRequest $request, Discussion $discussion)
    {
        return DiscussionResource::make($this->discussions->update(
            $discussion,
            $this->grants->actor(
                $this->currentUser($request)['user_uid'],
                $request->header(AgentGrantService::HEADER),
            ),
            $request->validated(),
        ));
    }

    /**
     * 删除话题或回复（软删除）
     *
     * 作者本人或挂靠对象的管理者（句子 / 逐词所在 channel、文章的编辑者）能删；
     * v2 只允许作者。删话题不动回复：有回复的话题以墓碑形式留在列表里。
     * 返回删除后的资源（200），不是 204——软删的资源并没有消失。
     *
     * @responseStatus 403 既不是作者，也不是挂靠对象的管理者
     *
     * @urlParam discussion string required 话题或回复的 uuid
     */
    public function destroy(Request $request, Discussion $discussion)
    {
        return DiscussionResource::make($this->discussions->delete(
            $discussion,
            $this->grants->actor(
                $this->currentUser($request)['user_uid'],
                $request->header(AgentGrantService::HEADER),
            ),
        ));
    }
}
