<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexMeGroupRequest;
use App\Http\Requests\V3\StoreMeGroupRequest;
use App\Http\Resources\V3\MeGroupResource;
use App\Services\V3\GroupInviteService;
use App\Services\V3\GroupMemberService;

/**
 * 我加入的团队（团队空间的候选身份）。
 *
 * 取代 `GET /v2/group-member?view=user`（`GroupMemberController@index`）、
 * `GET /v2/group?view=studio&view2=…` 与 `GET /v2/group-my-number`（`GroupController`）：
 * 「我创建的 / 我参加的」用 `owned` 过滤，数量读 `meta.total`。v2 那几份在
 * dashboard-v4 下线前原样保留，不要改动。
 *
 * 凭邀请链接加入团队（POST）是新能力，没有对应的 v2 端点；链接的签发与规则见
 * {@see GroupInviteService}。
 */
class MeGroupController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(
        private readonly GroupMemberService $members,
        private readonly GroupInviteService $invites,
    ) {}

    /**
     * 列出我加入的团队
     *
     * 每条带我在团队里的角色（owner / manager / editor / reader），按加入时间排序。
     *
     * @queryParam owned boolean true 只列我创建的，false 只列我参加的别人的团队；不传列全部
     * @queryParam search string 按团队名模糊匹配
     * @queryParam page integer 页码。Default: 1
     * @queryParam per_page integer 每页数量，最大 200。Default: 100
     */
    public function index(IndexMeGroupRequest $request)
    {
        $filters = $request->safe()->only(['search']);
        if ($request->has('owned')) {
            $filters['owned'] = $request->boolean('owned');
        }

        return MeGroupResource::collection($this->members->listForUser(
            $this->currentUser($request)['user_uid'],
            $filters,
            $request->integer('per_page', 100),
        ));
    }

    /**
     * 凭邀请链接加入团队
     *
     * `invite` 是邀请链接里的 token（见 `POST /v3/groups/{group}/invite-links`），
     * 加入后角色是 reader（只读）。已经是成员时原样返回现有的成员身份（200，角色不变）。
     * 只给人类账号，AI 模型的 token 403。
     *
     * @bodyParam invite string required 邀请链接里的 token
     *
     * @responseStatus 201 已加入
     * @responseStatus 403 用 AI 模型的 token 调用
     */
    public function store(StoreMeGroupRequest $request)
    {
        return MeGroupResource::make($this->invites->accept(
            $request->validated('invite'),
            $this->currentUser($request)['user_uid'],
        ));
    }
}
