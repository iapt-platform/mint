// src/api/password-reset.ts
//
// 找回密码。**已完全切到 v3**（取代 /v2/auth/forgot-password 与
// /v2/auth/reset-password），没有 v2 分支，要回退就 revert 那个 commit。

import { api, unwrapQuiet, unwrapVoid } from "./client";
import type { paths } from "./schema";

type RequestBody = NonNullable<
  paths["/v3/password-resets"]["post"]["requestBody"]
>["content"]["application/json"];

type MailLang = NonNullable<RequestBody["lang"]>;

/** 一次待完成的重置：给哪个账号、何时过期 */
export type PasswordReset = NonNullable<
  paths["/v3/password-resets/{token}"]["get"]["responses"][200]["content"]["application/json"]["data"]
>;

const MAIL_LANGS: readonly MailLang[] = ["en", "en-US", "zh-Hans", "zh-Hant"];

/** 界面语言没有对应邮件模板时不传，后端回落 en */
const toMailLang = (lang: string): MailLang | undefined =>
  MAIL_LANGS.find((l) => l === lang);

/**
 * 申请发送重置邮件。邮箱是否注册都会成功（后端不泄露账号是否存在）。
 */
export const requestPasswordReset = async (
  email: string,
  uiLang: string,
): Promise<void> =>
  unwrapVoid(
    await api.POST("/v3/password-resets", {
      body: { email, lang: toMailLang(uiLang) },
    }),
  );

/**
 * 凭邮件里的 token 查看待重置的账号。
 *
 * 用 unwrapQuiet：token 无效 / 过期（404）由重置页自己渲染成提示，不该再弹窗。
 */
export const fetchPasswordReset = async (
  token: string,
): Promise<PasswordReset> =>
  unwrapQuiet(
    await api.GET("/v3/password-resets/{token}", {
      params: { path: { token } },
    }),
  ).data ?? {};

/** 设新密码，成功后 token 作废 */
export const completePasswordReset = async (
  token: string,
  password: string,
  passwordConfirmation: string,
): Promise<void> =>
  unwrapVoid(
    await api.PATCH("/v3/password-resets/{token}", {
      params: { path: { token } },
      body: { password, password_confirmation: passwordConfirmation },
    }),
  );
