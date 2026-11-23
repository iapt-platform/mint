<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Resources\V3\GroupInviteLinkResource;
use App\Models\GroupInfo;
use App\Services\V3\GroupInviteService;
use Illuminate\Http\Request;

/**
 * 团队邀请链接：owner / manager 签一张链接发给别人，对方同意后以 reader 身份加入。
 *
 * 新能力，没有对应的 v2 端点。链接是签名 token、不落库，规则见 {@see GroupInviteService}。
 * 打开链接 `GET /v3/group-invites/{token}`，同意 `POST /v3/me/groups`。
 * 不支持 AI 模型：成员管理只给人类账号。
 */
class GroupInviteLinkController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly GroupInviteService $invites) {}

    /**
     * 签一张团队邀请链接
     *
     * 团队的 owner / manager 才能调用。7 天有效，经它加入的成员角色是 reader（只读）。
     * 链接不能单独作废：签发人被移出团队或降为 editor / reader 时，他签的链接全部失效。
     *
     * @urlParam group string required 团队 uid
     *
     * @responseStatus 201 签发成功
     * @responseStatus 403 不能管理这个团队的成员，或用 AI 模型的 token 调用
     */
    public function store(Request $request, GroupInfo $group)
    {
        return GroupInviteLinkResource::make($this->invites->issue(
            $group->uid,
            $this->currentUser($request)['user_uid'],
        ))->response()->setStatusCode(201);
    }
}
