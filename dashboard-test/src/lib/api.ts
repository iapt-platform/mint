import type { APIRequestContext } from '@playwright/test';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { BASE } from './env.js';

/** 直接调后端（走 dev server 的 /api 代理）：准备与清理测试数据用，不经过页面 */
const API = new URL('/api', BASE).toString();

const __dirname = path.dirname(fileURLToPath(import.meta.url));

/**
 * 登录态缓存。`user` 是 E2E_USER（auth.setup 必登），
 * `student` / `assistant` 是可选的第二、第三个账号（配了 E2E_STUDENT_* / E2E_ASSISTANT_* 才会登录）。
 */
export type Account = 'user' | 'student' | 'assistant';

export const authFile = (who: Account = 'user') =>
  path.join(__dirname, `../../playwright/.auth/${who}.json`);

/** setup 项目登录后存下的 token（v6 放在 localStorage 的 token 里） */
export function token(who: Account = 'user'): string {
  const file = authFile(who);
  if (!fs.existsSync(file)) throw new Error(`${file} 不存在，setup 是否跑过？账号是否配置？`);
  const state = JSON.parse(fs.readFileSync(file, 'utf8')) as {
    origins: { localStorage: { name: string; value: string }[] }[];
  };
  const t = state.origins.flatMap((o) => o.localStorage).find((i) => i.name === 'token')?.value;
  if (!t) throw new Error(`${file} 里没有 token，setup 是否跑过？`);
  return t;
}

/** `path` 从版本前缀写起，如 `/v3/discussions`、`/v2/sentence/{uid}`；`who` 指定以哪个账号的身份调用 */
export async function call(
  request: APIRequestContext,
  method: string,
  path: string,
  data?: object,
  who: Account = 'user',
) {
  const res = await request.fetch(`${API}${path}`, {
    method,
    data,
    headers: { Authorization: `Bearer ${token(who)}`, Accept: 'application/json' },
  });
  return { status: res.status(), body: await res.json().catch(() => null) };
}
