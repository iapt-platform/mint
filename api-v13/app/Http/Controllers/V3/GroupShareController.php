<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexGroupShareRequest;
use App\Http\Resources\V3\GroupShareResource;
use App\Models\GroupInfo;
use App\Models\Share;
use App\Services\V3\GroupShareService;
use Illuminate\Http\Request;

/**
 * 团队文档：共享给团队的资源。
 *
 * 取代 `GET /v2/share?view=group`（`ShareController@index`）与团队页上经
 * `DELETE /v2/share/{id}` 把资源移出团队。资源本身的协作者管理仍走 v2 share。
 * v2 那份在 dashboard-v4 下线前原样保留，不要改动。
 */
class GroupShareController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly GroupShareService $shares) {}

    /**
     * 列出团队文档
     *
     * 只有团队成员看得到。每条带资源类型、标题与拥有者，以及团队对它的权限（reader / editor）。
     *
     * @urlParam group string required 团队 uid
     *
     * @queryParam page integer 页码。Default: 1
     * @queryParam per_page integer 每页数量，最大 200。Default: 20
     *
     * @responseStatus 403 不是这个团队的成员
     */
    public function index(IndexGroupShareRequest $request, GroupInfo $group)
    {
        return GroupShareResource::collection($this->shares->list(
            $group,
            $this->currentUser($request)['user_uid'],
            $request->integer('per_page', 20),
        ));
    }

    /**
     * 把资源移出团队
     *
     * 团队的 owner / manager，或资源的拥有者才能调用。只删这条共享，资源本身不动。
     *
     * @urlParam group string required 团队 uid
     * @urlParam share string required 共享记录 id
     *
     * @responseStatus 204 已移出，无响应体
     * @responseStatus 403 不能移出这项资源
     */
    public function destroy(Request $request, GroupInfo $group, Share $share)
    {
        $this->shares->remove($group, $share, $this->currentUser($request)['user_uid']);

        return response()->noContent();
    }
}
