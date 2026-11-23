<?php

namespace App\Services\V3;

use App\Services\UserService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * 团队邀请链接：owner / manager 签一张链接，别人打开、同意后以 reader 身份加入团队。
 *
 * 新能力，没有对应的 v2 端点（v2 只能由管理者逐个加人）。
 *
 * 链接是签名 token，不落库（2026-11-23 用户定）：
 * - 载荷里写团队 uid、签发人、角色、过期时间，签名密钥与登录 token、agent 授权都分开；
 * - 发出去的链接不能单独作废，只能等它过期（{@see TTL}）；
 * - 接受时再查一次签发人：已不在团队里、或已不是 owner / manager，链接一并失效——
 *   降级或移除签发人，就撤销了他发出的所有链接。
 *
 * 只给人类账号：成员管理就是权限本身（同 {@see GroupMemberService}），模型 token 签发、接受都 403。
 */
class GroupInviteService
{
    /** 链接有效期 */
    public const TTL = 60 * 60 * 24 * 7;

    /** 经链接加入的角色：只读。要更高的角色，加入后由管理者改 */
    public const ROLE = GroupMemberService::DEFAULT_ROLE;

    private const TYPE = 'group-invite';

    public function __construct(
        private readonly GroupAbilityService $abilities,
        private readonly AgentGrantService $agents,
        private readonly UserService $users,
        private readonly GroupMemberService $members,
        private readonly GroupService $groups,
    ) {}

    /**
     * 签一张邀请链接。
     *
     * @return array{token: string, role: string, expires_at: string}
     *
     * @throws AuthorizationException 调用者不能管理成员，或是 AI 模型
     */
    public function issue(string $groupUid, string $actorUid): array
    {
        $this->agents->assertHuman($actorUid);
        $this->authorizeInviter($groupUid, $actorUid);

        $expiresAt = time() + self::TTL;
        $token = JWT::encode([
            'typ' => self::TYPE,
            'grp' => $groupUid,
            'iss' => $actorUid,
            'role' => self::ROLE,
            'nbf' => time(),
            'exp' => $expiresAt,
        ], $this->key(), 'HS512');

        return [
            'token' => $token,
            'role' => self::ROLE,
            'expires_at' => gmdate('Y-m-d\TH:i:s\Z', $expiresAt),
        ];
    }

    /**
     * 打开链接看到的内容：哪个团队、谁邀请的、以什么角色加入；登录了再告诉他是不是已经在团队里。
     *
     * @return array{group: Model, inviter: array<string, mixed>, role: string, expires_at: string, member: Model|null}
     *
     * @throws NotFoundHttpException 链接无效、过期，或签发人已无权邀请
     */
    public function preview(string $token, ?string $viewerUid): array
    {
        $invite = $this->resolve($token)
            ?? throw new NotFoundHttpException(__('messages.group_invite_invalid'));

        return [
            'group' => $invite['group'],
            'inviter' => $this->users->byUuid($invite['inviter_uid']),
            'role' => $invite['role'],
            'expires_at' => $invite['expires_at'],
            'member' => $viewerUid === null
                ? null
                : $this->abilities->membership($viewerUid, $invite['group']->uid),
        ];
    }

    /**
     * 同意邀请，加入团队。已经是成员就原样返回那条成员记录（角色不变，不会被降成 reader）。
     *
     * @throws ValidationException 链接无效、过期，或签发人已无权邀请
     * @throws AuthorizationException 调用者是 AI 模型
     */
    public function accept(string $token, string $userUid): Model
    {
        $this->agents->assertHuman($userUid);
        $invite = $this->resolve($token)
            ?? throw ValidationException::withMessages(['invite' => __('messages.group_invite_invalid')]);

        // 链接代表签发人拉人：以签发人身份加（resolve 已确认他仍能管理成员）
        return $this->members->join($invite['group'], $userUid, $invite['inviter_uid']);
    }

    /**
     * 验签并取出团队。伪造、过期、团队已删、签发人已无权邀请，一律 null（调用方报同一句「链接无效」）——
     * 不告诉外人团队还在不在、签发人是谁。
     *
     * @return array{group: Model, inviter_uid: string, role: string, expires_at: string}|null
     */
    private function resolve(string $token): ?array
    {
        try {
            $claims = JWT::decode($token, new Key($this->key(), 'HS512'));
        } catch (\Throwable) {
            return null;
        }
        if (($claims->typ ?? null) !== self::TYPE || empty($claims->grp) || empty($claims->iss)) {
            return null;
        }

        $group = $this->groups->find((string) $claims->grp);
        if ($group === null || ! $this->canInvite($group->uid, (string) $claims->iss)) {
            return null;
        }

        return [
            'group' => $group,
            'inviter_uid' => (string) $claims->iss,
            'role' => self::ROLE,
            'expires_at' => gmdate('Y-m-d\TH:i:s\Z', (int) $claims->exp),
        ];
    }

    /**
     * @throws AuthorizationException
     */
    private function authorizeInviter(string $groupUid, string $uid): void
    {
        if (! $this->canInvite($groupUid, $uid)) {
            throw new AuthorizationException(__('messages.group_forbidden'));
        }
    }

    /** 能发邀请 = 能管理成员（owner / manager） */
    private function canInvite(string $groupUid, string $uid): bool
    {
        return GroupAbilityService::roleAllows(
            $this->abilities->roleIn($uid, $groupUid),
            GroupAbilityService::MANAGE_MEMBERS,
        );
    }

    /** 独立的签名密钥：邀请链接不能当登录 token 或 agent 授权用，反之亦然 */
    private function key(): string
    {
        return hash('sha512', config('mint.app.jwt_secrets_key').'|'.self::TYPE);
    }
}
