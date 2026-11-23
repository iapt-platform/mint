<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Resources\V3\GroupInviteResource;
use App\Services\V3\GroupInviteService;
use Illuminate\Http\Request;

/**
 * 打开团队邀请链接：看是哪个团队、谁邀请的。
 *
 * 新能力，没有对应的 v2 端点。签发见 `POST /v3/groups/{group}/invite-links`，
 * 同意加入见 `POST /v3/me/groups`。
 */
class GroupInviteController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly GroupInviteService $invites) {}

    /**
     * 查看团队邀请
     *
     * 不登录也能看（私有团队也一样，链接本身就是凭证）；登录了 `member_role` 给出
     * 查看者在这个团队里现有的角色，还不是成员为 null。
     *
     * @unauthenticated
     *
     * @urlParam token string required 邀请链接里的 token
     *
     * @responseStatus 404 链接无效、已过期，或签发人已不能邀请
     */
    public function show(Request $request, string $token)
    {
        return GroupInviteResource::make($this->invites->preview($token, $this->currentUserUid($request)));
    }
}
