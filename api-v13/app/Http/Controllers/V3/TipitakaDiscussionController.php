<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexTipitakaDiscussionRequest;
use App\Http\Resources\V3\DiscussionResource;
use App\Services\V3\DiscussionService;

/**
 * 按经文范围列讨论：某个 channel 的一段经文（整个 channel / 一本书 / 一章 / 段落区间）里，
 * 挂在句子上的全部话题。给前端的章节讨论面板、agent 的「这一章还有哪些未关闭的 issue」用。
 *
 * 与 `GET /v3/discussions` 是两个契约：那边按单个对象查（target_type + target_id 必填），
 * 这边按 channel + 经文范围查，必填参数不同，所以分成两个端点（v3 规范第 1 条）。
 * 没有对应的 v2 端点；最接近的是 `POST /v2/sent-discussion-tree`（调用方逐句列出句子坐标，
 * 只回标题与回复数），v2 那份原样保留。
 */
class TipitakaDiscussionController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly DiscussionService $discussions) {}

    /**
     * 列出一段经文里的话题
     *
     * 只列挂在该 channel 句子上的话题（不含回复），按经文顺序（书、段、句）排列，同一句里按时间；
     * 每条带 `sentence`（所在句子的坐标）。删除了但还有回复的话题以墓碑出现。
     * 不传 type 时只列评论线程（discussion / qa / help / issue / glossary），批注要显式
     * type=note / commentary。可见性同 `GET /v3/discussions`：未登录看不到 discussion；
     * basic 角色在自己不能编辑的 channel 上只看得到自己发起的 discussion。
     * channel 不存在或范围内没有讨论都返回空列表；chapter 不存在 404。
     *
     * @unauthenticated
     *
     * @responseStatus 404 chapter 不是本书存在的段落
     *
     * @urlParam channel string required 句子所在 channel 的 uuid
     *
     * @queryParam book integer 书号；与 chapter / para / to 一起用时必填，不传则是整个 channel。Example: 169
     * @queryParam chapter integer 章节起始段落号，服务端按章节长度展开成段落区间；不能与 para / to 同用。Example: 893
     * @queryParam para integer 起始段落号。Example: 895
     * @queryParam to integer 结束段落号（含），缺省同 para。Example: 896
     * @queryParam type string 类型，逗号分隔多选；不传则只列评论线程。Example: issue,glossary
     * @queryParam status string 话题状态，不传则两种都要。Enum: active,close
     * @queryParam severity string 严重程度，逗号分隔多选。Example: error,warning
     * @queryParam page integer 页码。Default: 1
     * @queryParam per_page integer 每页数量，最大 200。Default: 15
     */
    public function __invoke(IndexTipitakaDiscussionRequest $request, string $channel)
    {
        return DiscussionResource::collection($this->discussions->listInChannel(
            $channel,
            $request->validated(),
            $this->currentUserUid($request),
            $request->integer('per_page', 15),
        ));
    }
}
