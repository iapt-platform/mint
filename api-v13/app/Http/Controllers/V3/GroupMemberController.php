<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexGroupMemberRequest;
use App\Http\Requests\V3\StoreGroupMemberRequest;
use App\Http\Requests\V3\UpdateGroupMemberRequest;
use App\Http\Resources\V3\GroupMemberResource;
use App\Models\GroupInfo;
use App\Models\GroupMember;
use App\Services\V3\GroupMemberService;
use Illuminate\Http\Request;

/**
 * 团队成员：列表、加人、改角色、移人 / 退出。
 *
 * 取代 `GET /v2/group-member?view=group`、`POST /v2/group-member`、
 * `DELETE /v2/group-member/{id}`（`GroupMemberController`）；v2 的 PUT 是空实现，
 * 改角色是 v3 新增。v2 那份在 dashboard-v4 下线前原样保留，不要改动。
 *
 * 不支持 AI 模型代写（2026-11-13 定）：成员管理就是权限本身，只给人类账号。
 */
class GroupMemberController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly GroupMemberService $members) {}

    /**
     * 列出团队成员
     *
     * 只有团队成员看得到。owner、manager 在前，其余按加入时间排序。
     *
     * @urlParam group string required 团队 uid
     *
     * @queryParam page integer 页码。Default: 1
     * @queryParam per_page integer 每页数量，最大 200。Default: 100
     *
     * @responseStatus 403 不是这个团队的成员
     */
    public function index(IndexGroupMemberRequest $request, GroupInfo $group)
    {
        return GroupMemberResource::collection($this->members->listForGroup(
            $group,
            $this->currentUser($request)['user_uid'],
            $request->integer('per_page', 100),
        ));
    }

    /**
     * 加一名成员
     *
     * 团队的 owner / manager 才能调用。角色默认 reader（只读）。
     *
     * @urlParam group string required 团队 uid
     *
     * @bodyParam user_id string required 要加入的用户 uuid
     * @bodyParam role string 角色，默认 reader。Enum: manager,editor,reader
     *
     * @responseStatus 201 加入成功
     * @responseStatus 403 不能管理这个团队的成员
     */
    public function store(StoreGroupMemberRequest $request, GroupInfo $group)
    {
        return GroupMemberResource::make($this->members->add(
            $group,
            $this->currentUser($request)['user_uid'],
            $request->validated(),
        ));
    }

    /**
     * 改成员角色
     *
     * 团队的 owner / manager 才能调用；owner 那一行不能改。
     *
     * @urlParam group string required 团队 uid
     * @urlParam member string required 成员记录 id
     *
     * @bodyParam role string 角色。Enum: manager,editor,reader
     *
     * @responseStatus 403 不能管理这个团队的成员，或目标是团队拥有者
     */
    public function update(UpdateGroupMemberRequest $request, GroupInfo $group, GroupMember $member)
    {
        return GroupMemberResource::make($this->members->update(
            $group,
            $member,
            $this->currentUser($request)['user_uid'],
            $request->validated(),
        ));
    }

    /**
     * 移除成员 / 退出团队
     *
     * 移除别人要 owner / manager；移除自己就是退出团队。owner 不能被移除、也不能退出。
     * 硬删，成功返回 204 空体。
     *
     * @urlParam group string required 团队 uid
     * @urlParam member string required 成员记录 id
     *
     * @responseStatus 204 已移除，无响应体
     * @responseStatus 403 不能移除这名成员
     */
    public function destroy(Request $request, GroupInfo $group, GroupMember $member)
    {
        $this->members->remove($group, $member, $this->currentUser($request)['user_uid']);

        return response()->noContent();
    }
}
