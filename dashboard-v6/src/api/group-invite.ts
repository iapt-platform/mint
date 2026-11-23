/**
 * 团队邀请链接（v3）：签发、打开、同意加入。
 *
 * 新能力，没有对应的 v2 端点。链接是服务端签名的 token（7 天有效、不落库），
 * 经链接加入的成员一律是 reader；签发人被降级或移出团队时，他签的链接全部失效。
 */
import { api, unwrap, unwrapQuiet } from "./client";
import type { paths } from "./schema";
import { fullUrl } from "../utils";

type GroupInviteResponse =
  paths["/v3/group-invites/{token}"]["get"]["responses"][200]["content"]["application/json"];
type GroupInviteLinkResponse =
  paths["/v3/groups/{group}/invite-links"]["post"]["responses"][201]["content"]["application/json"];

export type GroupInvite = NonNullable<GroupInviteResponse["data"]>;
export type GroupInviteLink = NonNullable<GroupInviteLinkResponse["data"]>;

/** 邀请页的完整地址（发给别人用），路由见 Router.tsx 的 team-invite/:token */
export const groupInviteUrl = (token: string): string =>
  fullUrl(`/team-invite/${token}`);

/** 签一张邀请链接（owner / manager） */
export const createGroupInviteLink = async (
  group: string
): Promise<GroupInviteLink | undefined> =>
  unwrap(
    await api.POST("/v3/groups/{group}/invite-links", {
      params: { path: { group } },
    })
  ).data;

/** 打开链接。无效 / 过期是 404，由邀请页自己渲染，不弹全局提示 */
export const fetchGroupInvite = async (
  token: string
): Promise<GroupInvite | undefined> =>
  unwrapQuiet(
    await api.GET("/v3/group-invites/{token}", {
      params: { path: { token } },
    })
  ).data;

/** 同意加入，返回加入后的「我的团队」条目；已是成员时原样返回现有身份 */
export const acceptGroupInvite = async (token: string) =>
  unwrap(
    await api.POST("/v3/me/groups", {
      body: { invite: token },
    })
  ).data;
