<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexChannelRequest;
use App\Http\Requests\V3\UpdateChannelRequest;
use App\Http\Resources\V3\ChannelResource;
use App\Models\Channel;
use App\Services\V3\ChannelService;
use Illuminate\Http\Request;

/**
 * channel：公开目录、详情、改信息、删除。
 *
 * 取代 `GET /v2/channel?view=public|system`、`GET|PUT|PATCH|DELETE /v2/channel/{channel}`
 * （`ChannelController`）。某个 studio 的列表与新建见 {@see StudioChannelController}，
 * 我能写的见 {@see EditableChannelController}。v2 那份在 dashboard-v4 下线前原样保留，不要改动。
 *
 * channels 表不支持 AI 模型代写（2026-11-16 定）：模型 token 调改、删一律 403。
 */
class ChannelController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly ChannelService $channels) {}

    /**
     * 公开 channel 目录
     *
     * 只列全网公开的。`system=true` 只要系统 channel——系统 channel 是全站共用的原文、术语等，
     * 不论 status 都列（巴利原文 `_System_Pali_VRI_` 就是 status 10）。带 `book` + `para` 时每条多返回该章的完成度
     * `progress`，并可按它排序。
     *
     * @unauthenticated
     *
     * @queryParam system boolean 只列系统 channel
     * @queryParam search string 按名字模糊匹配
     * @queryParam type string channel 类型。Enum: translation,nissaya,original,wbw,commentary,similar
     * @queryParam lang string 语言
     * @queryParam book integer 章节所在的书（与 para 同时给）
     * @queryParam para integer 章节起始段（与 book 同时给）
     * @queryParam order string 排序字段。Enum: updated_at,created_at,name,progress Default: updated_at
     * @queryParam dir string 排序方向。Enum: asc,desc Default: desc
     * @queryParam page integer 页码。Default: 1
     * @queryParam per_page integer 每页数量，最大 200。Default: 20
     */
    public function index(IndexChannelRequest $request)
    {
        $filters = $request->safe()->except(['system', 'page', 'per_page']);
        $filters['system'] = $request->boolean('system');

        return ChannelResource::collection($this->channels->listPublic(
            $filters,
            $this->currentUserUid($request),
            $request->integer('per_page', 20),
        ));
    }

    /**
     * channel 详情
     *
     * 公开的谁都能看；私有的只有拥有者、团队成员、被共享的人能看。带查看者的 `abilities`。
     *
     * @unauthenticated
     *
     * @urlParam channel string required channel uid
     *
     * @responseStatus 403 私有 channel，且看不见
     */
    public function show(Request $request, Channel $channel)
    {
        return ChannelResource::make($this->channels->show($channel, $this->currentUserUid($request)));
    }

    /**
     * 改 channel 信息
     *
     * 只改传了的字段。拥有者；团队的 channel 由团队 owner / manager / editor 改；共享权限 ≥30 也能改。
     * 系统 channel 不能改。AI 模型不能改。
     *
     * @urlParam channel string required channel uid
     *
     * @bodyParam name string 名字，最长 128；同一 studio 内不能重名
     * @bodyParam type string 类型。Enum: translation,nissaya,original,wbw,commentary,similar
     * @bodyParam lang string 语言，最长 16
     * @bodyParam summary string 简介，最长 1024
     * @bodyParam status integer 10 私有、20 公开不列出（拿到链接能看，不进任何公开列表）、30 公开
     * @bodyParam source_type string 来源。Enum: original,reprint,ai
     * @bodyParam source_id string 来源 id
     * @bodyParam config object 配置
     *
     * @responseStatus 403 不能修改
     */
    public function update(UpdateChannelRequest $request, Channel $channel)
    {
        return ChannelResource::make($this->channels->update(
            $channel,
            $this->currentUser($request)['user_uid'],
            $request->validated(),
        ));
    }

    /**
     * 删除 channel
     *
     * 软删除，返回被删的 channel。个人 channel 由拥有者删，团队的只有团队 owner 能删。
     * channel 下已有译文、术语或逐词时拒绝（409，`content` 列出有哪几类）。系统 channel 不能删。
     *
     * @urlParam channel string required channel uid
     *
     * @responseStatus 403 不能删除
     * @responseStatus 409 channel 下还有内容
     */
    public function destroy(Request $request, Channel $channel)
    {
        return ChannelResource::make($this->channels->delete(
            $channel,
            $this->currentUser($request)['user_uid'],
        ));
    }
}
