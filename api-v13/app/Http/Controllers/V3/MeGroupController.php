<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexMeGroupRequest;
use App\Http\Resources\V3\MeGroupResource;
use App\Services\V3\GroupMemberService;

/**
 * 我加入的团队（团队空间的候选身份）。
 *
 * 取代 `GET /v2/group-member?view=user`（`GroupMemberController@index`）、
 * `GET /v2/group?view=studio&view2=…` 与 `GET /v2/group-my-number`（`GroupController`）：
 * 「我创建的 / 我参加的」用 `owned` 过滤，数量读 `meta.total`。v2 那几份在
 * dashboard-v4 下线前原样保留，不要改动。
 */
class MeGroupController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly GroupMemberService $members) {}

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
    public function __invoke(IndexMeGroupRequest $request)
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
}
