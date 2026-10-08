// src/api/session.ts
//
// 登录与「我是谁」。**已完全切到 v3**（取代 /v2/sign-in 与 /v2/auth/current），
// 没有 v2 分支，要回退就 revert 那个 commit。

import { api, unwrapQuiet } from "./client";
import type { paths } from "./schema";
import type { IUser } from "../reducers/current-user";

type Me = NonNullable<
  paths["/v3/me"]["get"]["responses"][200]["content"]["application/json"]["data"]
>;

/**
 * v3 的用户资料 → store 里的 IUser。
 *
 * 字段名本来就一致（v3 沿用了驼峰的用户摘要口径），这里只把规格里的可选 / 可空
 * 收成 IUser 要的必填值。
 */
const toUser = (me: Me): IUser => ({
  id: me.id ?? "",
  nickName: me.nickName ?? "",
  realName: me.realName ?? "",
  avatar: me.avatar ?? "",
  roles: me.roles ?? [],
});

/**
 * 用户名或邮箱 + 密码登录，一次请求同时拿到 token 与用户资料。
 *
 * 用 unwrapQuiet：账号密码不对（422）由登录表单自己显示，不该再弹窗。
 */
export const signInWithPassword = async (
  login: string,
  password: string,
): Promise<{ token: string; user: IUser }> => {
  const data = unwrapQuiet(
    await api.POST("/v3/sessions", { body: { login, password } }),
  ).data;
  return { token: data?.token ?? "", user: toUser(data?.user ?? {}) };
};

/**
 * 用已存的 token 取当前用户（应用启动时恢复登录态）。
 *
 * 用 unwrapQuiet：401 是「token 失效」的正常信号，由调用方清掉 token，不弹窗。
 */
export const fetchMe = async (): Promise<IUser> =>
  toUser(unwrapQuiet(await api.GET("/v3/me")).data ?? {});
