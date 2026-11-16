/**
 * 团队成员（v3）：我加入的团队、成员列表、加人、改角色、移人 / 退出。
 *
 * 取代 v2 的 `group-member`（`view=user` / `view=group`、POST、DELETE）。
 * 团队本身的建、改、删、搜索与团队文档见 `group.ts`。
 */
import { api, unwrap, unwrapQuiet, unwrapVoid } from "./client";
import type { paths } from "./schema";

type MeGroupList =
  paths["/v3/me/groups"]["get"]["responses"][200]["content"]["application/json"];
type GroupMemberList =
  paths["/v3/groups/{group}/members"]["get"]["responses"][200]["content"]["application/json"];

/** 我加入的一个团队，带我在里面的角色 */
export type MyGroup = NonNullable<MeGroupList["data"]>[number];
export type GroupMember = NonNullable<GroupMemberList["data"]>[number];
export type GroupMemberListMeta = GroupMemberList["meta"];

/** 团队角色。owner 只在建团队时产生，不能分配 */
export type GroupRole = "owner" | "manager" | "editor" | "reader";
export type AssignableGroupRole = Exclude<GroupRole, "owner">;
export const ASSIGNABLE_GROUP_ROLES: AssignableGroupRole[] = [
  "manager",
  "editor",
  "reader",
];

/** 能加人、移人、改角色的角色 */
export const canManageMembers = (role?: string): boolean =>
  role === "owner" || role === "manager";

/** 一个人加入的团队不会多，一页取全 */
const MY_GROUPS_PAGE_SIZE = 200;

/** 启动时后台取，失败不弹窗（调用方自己兜底） */
export const fetchMyGroups = async (): Promise<MyGroup[]> =>
  unwrapQuiet(
    await api.GET("/v3/me/groups", {
      params: { query: { per_page: MY_GROUPS_PAGE_SIZE } },
    })
  ).data ?? [];

/** 「我的团队」分页列表：owned=true 我创建的、false 我参加的；数量读 meta.total */
export const fetchMyGroupsPage = async (query: {
  owned?: boolean;
  search?: string;
  page?: number;
  per_page?: number;
}) =>
  unwrap(
    await api.GET("/v3/me/groups", {
      params: { query },
    })
  );

export const fetchGroupMembers = async (
  group: string,
  page: number,
  perPage: number
) =>
  unwrap(
    await api.GET("/v3/groups/{group}/members", {
      params: { path: { group }, query: { page, per_page: perPage } },
    })
  );

export const addGroupMember = async (
  group: string,
  userId: string,
  role?: AssignableGroupRole
) =>
  unwrap(
    await api.POST("/v3/groups/{group}/members", {
      params: { path: { group } },
      body: { user_id: userId, role },
    })
  ).data;

export const updateGroupMemberRole = async (
  group: string,
  member: string,
  role: AssignableGroupRole
) =>
  unwrap(
    await api.PATCH("/v3/groups/{group}/members/{member}", {
      params: { path: { group, member } },
      body: { role },
    })
  ).data;

/** 移除成员；member 是自己那条就是退出团队 */
export const removeGroupMember = async (
  group: string,
  member: string
): Promise<void> =>
  unwrapVoid(
    await api.DELETE("/v3/groups/{group}/members/{member}", {
      params: { path: { group, member } },
    })
  );
