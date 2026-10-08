// src/api/sign-up.ts
//
// 注册。**已完全切到 v3**（取代 /v2/email-certification、/v2/invite/{id}、
// /v2/sign-up），没有 v2 分支，要回退就 revert 那个 commit。
//
// 自助注册：sendSignUpCode → exchangeCodeForInvite → createAccount
// 邀请注册：fetchInvite（邮件链接里的 invite）→ createAccount

import { api, unwrap, unwrapQuiet, unwrapVoid } from "./client";
import type { paths } from "./schema";

type CodeBody = NonNullable<
  paths["/v3/email-certifications"]["post"]["requestBody"]
>["content"]["application/json"];

type MailLang = NonNullable<CodeBody["lang"]>;

/** 注册凭据：id 就是 createAccount 要的 invite */
export type Invite = NonNullable<
  paths["/v3/invites/{invite}"]["get"]["responses"][200]["content"]["application/json"]["data"]
>;

export type AccountInput = NonNullable<
  paths["/v3/users"]["post"]["requestBody"]
>["content"]["application/json"];

export type Account = NonNullable<
  paths["/v3/users"]["post"]["responses"][201]["content"]["application/json"]["data"]
>;

const MAIL_LANGS: readonly MailLang[] = ["en", "en-US", "zh-Hans", "zh-Hant"];

/** 界面语言没有对应邮件模板时不传，后端回落 en */
const toMailLang = (lang: string): MailLang | undefined =>
  MAIL_LANGS.find((l) => l === lang);

/** 给待注册的邮箱发 6 位验证码。邮箱已注册时 422（errors.email） */
export const sendSignUpCode = async (
  email: string,
  uiLang: string,
): Promise<void> =>
  unwrapVoid(
    await api.POST("/v3/email-certifications", {
      body: { email, lang: toMailLang(uiLang) },
    }),
  );

/** 服务端比对验证码，通过则拿到 invite。验证码不对时 422（errors.code） */
export const exchangeCodeForInvite = async (
  email: string,
  code: string,
): Promise<Invite> =>
  unwrap(await api.POST("/v3/invites", { body: { email, code } })).data ?? {};

/**
 * 邀请注册页：凭邮件链接里的 invite 取邮箱。
 *
 * 用 unwrapQuiet：邀请无效 / 已用过（404）由页面自己渲染成提示，不该再弹窗。
 */
export const fetchInvite = async (invite: string): Promise<Invite> =>
  unwrapQuiet(
    await api.GET("/v3/invites/{invite}", {
      params: { path: { invite } },
    }),
  ).data ?? {};

/** 凭 invite 建账号。注册不等于登录，之后走登录页 */
export const createAccount = async (input: AccountInput): Promise<Account> =>
  unwrap(await api.POST("/v3/users", { body: input })).data ?? {};
