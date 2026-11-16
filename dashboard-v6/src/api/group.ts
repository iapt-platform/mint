/**
 * 团队本身（v3）与团队文档（共享给团队的资源）。
 *
 * 取代 v2 的 `group`（增删改查、`view=all`）与 `share?view=group`。
 * 「我的团队」与成员管理见 `group-member.ts`。
 */
import type { LoaderFunctionArgs } from "react-router";

import { api, unwrap, unwrapQuiet, unwrapVoid } from "./client";
import type { paths } from "./schema";

type GroupSingle =
  paths["/v3/groups/{group}"]["get"]["responses"][200]["content"]["application/json"];
type GroupShareList =
  paths["/v3/groups/{group}/shares"]["get"]["responses"][200]["content"]["application/json"];

export type Group = NonNullable<GroupSingle["data"]>;
export type GroupShare = NonNullable<GroupShareList["data"]>[number];

/** 团队可见性：10 私有（只有成员看得到）、30 公开 */
export const GROUP_STATUS_PRIVATE = 10;
export const GROUP_STATUS_PUBLIC = 30;

export type NewGroupInput =
  paths["/v3/groups"]["post"]["requestBody"]["content"]["application/json"];
export type GroupInput = NonNullable<
  paths["/v3/groups/{group}"]["patch"]["requestBody"]
>["content"]["application/json"];

/** 别处（共享、课程等）仍在用的旧类型，与 v2 团队接口无关 */
export interface IGroup {
  id: string;
  name: string;
}
export interface IDeleteResponse {
  ok: boolean;
  message: string;
  data: number;
}

export const fetchGroup = async (group: string): Promise<Group> =>
  unwrap(await api.GET("/v3/groups/{group}", { params: { path: { group } } }))
    .data as Group;

/** 团队页路由的数据加载：给面包屑取团队名 */
export async function groupLoader({ params }: LoaderFunctionArgs) {
  const group = params.teamId;
  if (!group) {
    throw new Response("Missing teamId", { status: 400 });
  }
  try {
    return unwrapQuiet(
      await api.GET("/v3/groups/{group}", { params: { path: { group } } })
    ).data;
  } catch {
    throw new Response("team not found", { status: 404 });
  }
}

/** 按名字搜公开团队（共享资源时挑团队） */
export const searchGroups = async (search: string, perPage = 20) =>
  unwrap(
    await api.GET("/v3/groups", {
      params: { query: { search, per_page: perPage } },
    })
  ).data ?? [];

export const createGroup = async (body: NewGroupInput) =>
  unwrap(await api.POST("/v3/groups", { body })).data;

export const updateGroup = async (group: string, body: GroupInput) =>
  unwrap(
    await api.PATCH("/v3/groups/{group}", {
      params: { path: { group } },
      body,
    })
  ).data;

/** 名下还有资源时后端 409，unwrapVoid 会弹出原因 */
export const deleteGroup = async (group: string): Promise<void> =>
  unwrapVoid(
    await api.DELETE("/v3/groups/{group}", { params: { path: { group } } })
  );

export const fetchGroupShares = async (
  group: string,
  page: number,
  perPage: number
) =>
  unwrap(
    await api.GET("/v3/groups/{group}/shares", {
      params: { path: { group }, query: { page, per_page: perPage } },
    })
  );

/** 把资源移出团队（只删这条共享，资源本身不动） */
export const removeGroupShare = async (
  group: string,
  share: string
): Promise<void> =>
  unwrapVoid(
    await api.DELETE("/v3/groups/{group}/shares/{share}", {
      params: { path: { group, share } },
    })
  );
