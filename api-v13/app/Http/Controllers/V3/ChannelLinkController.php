<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\UpdateChannelLinksRequest;
use App\Http\Resources\V3\ChannelLinksResource;
use App\Models\Channel;
use App\Services\V3\ChannelLinkService;
use Illuminate\Http\Request;

/**
 * channel 之间的关系设置：继承自、降级、捆绑的逐词、术语路径（单例资源，每个 channel 一份）。
 *
 * 新能力，没有对应的 v2 端点。服务端只记录，降级的拼接、术语的取用由客户端做。
 */
class ChannelLinkController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly ChannelLinkService $links) {}

    /**
     * channel 的关系设置
     *
     * 能读这个 channel 的人都能看。指向的 channel 已删除、或查看者看不见的，不出现在结果里。
     * `can_update` 表示查看者能不能改。
     *
     * @unauthenticated
     *
     * @urlParam channel string required channel uid
     *
     * @responseStatus 403 私有 channel，且看不见
     */
    public function show(Request $request, Channel $channel)
    {
        return ChannelLinksResource::make($this->links->get($channel, $this->currentUserUid($request)));
    }

    /**
     * 改 channel 的关系设置
     *
     * 传了哪个键就整体替换哪一种关系，没传的不动；单值传 null 清掉，列表传 [] 清空。
     * 能改 channel 信息的人才能改（拥有者、团队 owner / manager / editor、共享权限 ≥30）。AI 模型不能改。
     * 目标必须是你看得见的 channel，且不能是自己。降级版本必须与本 channel 同类型；
     * 术语路径只有 translation channel 能设，目标也只能是 translation（传 [] 清空总是允许）。
     *
     * @urlParam channel string required channel uid
     *
     * @bodyParam derived_from string|null 继承自哪个 channel（如英文版译自中文版），只记录
     * @bodyParam wbw string|null 捆绑的逐词 channel，必须是 wbw 类型；载入时当原文
     * @bodyParam fallback string[] 降级 channel，有序，最多 3 个，须与本 channel 同类型；段落没有数据时由客户端改取
     * @bodyParam fallback.* string channel uid
     * @bodyParam term_source string[] 术语路径，有序，最多 10 个，只能是 translation；只有 translation channel 能设
     * @bodyParam term_source.* string channel uid
     *
     * @responseStatus 403 不能修改
     */
    public function update(UpdateChannelLinksRequest $request, Channel $channel)
    {
        return ChannelLinksResource::make($this->links->update(
            $channel,
            $this->currentUser($request)['user_uid'],
            $request->validated(),
        ));
    }
}
