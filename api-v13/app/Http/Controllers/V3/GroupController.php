<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexGroupRequest;
use App\Http\Requests\V3\StoreGroupRequest;
use App\Http\Requests\V3\UpdateGroupRequest;
use App\Http\Resources\V3\GroupResource;
use App\Models\GroupInfo;
use App\Services\V3\GroupService;
use Illuminate\Http\Request;

/**
 * 团队本身：搜公开团队、详情、建、改、删。
 *
 * 取代 `GET /v2/group?view=all`、`GET|POST|PUT|DELETE /v2/group(/{id})`（`GroupController`）。
 * 「我的团队」列表见 {@see MeGroupController}。v2 那份在 dashboard-v4 下线前原样保留，不要改动。
 *
 * 不支持 AI 模型代写（2026-11-13 定）。
 */
class GroupController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly GroupService $groups) {}

    /**
     * 搜公开团队
     *
     * 共享资源时挑团队用。只列公开团队（status 30），按名字排序。
     *
     * @queryParam search string 按团队名模糊匹配
     * @queryParam page integer 页码。Default: 1
     * @queryParam per_page integer 每页数量，最大 200。Default: 20
     */
    public function index(IndexGroupRequest $request)
    {
        return GroupResource::collection($this->groups->searchPublic(
            $request->validated('search'),
            $request->integer('per_page', 20),
        ));
    }

    /**
     * 建团队
     *
     * 建的人成为 owner。同一个人名下不能重名。
     *
     * @bodyParam name string required 团队名，最长 64
     * @bodyParam description string 简介，最长 1024
     * @bodyParam status integer 10 私有、30 公开。Default: 30
     *
     * @responseStatus 201 建好了
     */
    public function store(StoreGroupRequest $request)
    {
        return GroupResource::make($this->groups->create(
            $this->currentUser($request)['user_uid'],
            $request->validated(),
        ));
    }

    /**
     * 团队详情
     *
     * 公开团队谁都能看（可不登录），私有团队只有成员能看。带查看者在团队里的角色。
     *
     * @unauthenticated
     *
     * @urlParam group string required 团队 uid
     *
     * @responseStatus 403 私有团队，且不是成员
     */
    public function show(Request $request, GroupInfo $group)
    {
        return GroupResource::make($this->groups->show($group, $this->currentUserUid($request)));
    }

    /**
     * 改团队信息
     *
     * 团队的 owner / manager 才能调用，只改传了的字段。
     *
     * @urlParam group string required 团队 uid
     *
     * @bodyParam name string 团队名，最长 64
     * @bodyParam description string 简介，最长 1024
     * @bodyParam status integer 10 私有、30 公开
     *
     * @responseStatus 403 不能修改这个团队
     */
    public function update(UpdateGroupRequest $request, GroupInfo $group)
    {
        return GroupResource::make($this->groups->update(
            $group,
            $this->currentUser($request)['user_uid'],
            $request->validated(),
        ));
    }

    /**
     * 删团队
     *
     * 只有 owner 能删。团队名下还有资源（channel、文章等）时拒绝，`resources` 里给出各类的条数；
     * 否则连同成员与共享给团队的记录一起硬删，返回 204 空体。
     *
     * @urlParam group string required 团队 uid
     *
     * @responseStatus 204 已删除，无响应体
     * @responseStatus 403 只有 owner 能删
     * @responseStatus 409 团队名下还有资源
     */
    public function destroy(Request $request, GroupInfo $group)
    {
        $this->groups->delete($group, $this->currentUser($request)['user_uid']);

        return response()->noContent();
    }
}
