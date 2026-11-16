<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexStudioChannelRequest;
use App\Http\Requests\V3\StoreChannelRequest;
use App\Http\Resources\V3\ChannelResource;
use App\Services\V3\ChannelService;

/**
 * 某个 studio（本人或团队）的 channel：工作区列表与新建。
 *
 * 取代 `GET /v2/channel?view=studio|studio-all`（含 `view2`、`collaborator`）、`GET /v2/channel-my-number`
 * （数量读 `meta.total`）与 `POST /v2/channel`（`ChannelController`）。`{studio}` 一律是 uuid：
 * 用户 uuid 或团队 uuid（团队空间）。v2 那份在 dashboard-v4 下线前原样保留，不要改动。
 */
class StudioChannelController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly ChannelService $channels) {}

    /**
     * 列出 studio 的 channel
     *
     * 只有本人，或该团队的成员能看。`relation`：own 它名下的（默认），shared 直接共享给它的
     * （个人空间不含经团队转来的——团队的在团队空间里看），all 两者合起来。
     *
     * @urlParam studio string required 用户 uuid 或团队 uuid
     *
     * @queryParam relation string 关系。Enum: own,shared,all Default: own
     * @queryParam collaborator string 只看这个拥有者共享过来的（uuid）
     * @queryParam search string 按名字模糊匹配
     * @queryParam type string channel 类型。Enum: translation,nissaya,original,wbw,commentary,similar
     * @queryParam lang string 语言
     * @queryParam book integer 章节所在的书（与 para 同时给）
     * @queryParam para integer 章节起始段（与 book 同时给）
     * @queryParam order string 排序字段。Enum: updated_at,created_at,name,progress Default: updated_at
     * @queryParam dir string 排序方向。Enum: asc,desc Default: desc
     * @queryParam page integer 页码。Default: 1
     * @queryParam per_page integer 每页数量，最大 200。Default: 20
     *
     * @responseStatus 403 不能查看这个 studio
     */
    public function index(IndexStudioChannelRequest $request, string $studio)
    {
        return ChannelResource::collection($this->channels->listForStudio(
            $studio,
            $this->currentUser($request)['user_uid'],
            $request->safe()->except(['page', 'per_page']),
            $request->integer('per_page', 20),
        ));
    }

    /**
     * 在 studio 名下建 channel
     *
     * 本人名下，或团队名下（团队 owner / manager）。同一 studio 内不能重名。默认私有。AI 模型不能建。
     *
     * @urlParam studio string required 用户 uuid 或团队 uuid
     *
     * @bodyParam name string required 名字，最长 128
     * @bodyParam type string required 类型。Enum: translation,nissaya,original,wbw,commentary,similar
     * @bodyParam lang string required 语言，最长 16
     * @bodyParam summary string 简介，最长 1024
     * @bodyParam status integer 10 私有、20 公开不列出（拿到链接能看，不进任何公开列表）、30 公开。Default: 10
     * @bodyParam source_type string 来源。Enum: original,reprint,ai
     * @bodyParam source_id string 来源 id
     *
     * @responseStatus 201 建好了
     * @responseStatus 403 不能在这个 studio 名下建
     */
    public function store(StoreChannelRequest $request, string $studio)
    {
        $user = $this->currentUser($request);

        return ChannelResource::make($this->channels->create(
            $studio,
            $user['user_uid'],
            (int) $user['user_id'],
            $request->validated(),
        ));
    }
}
