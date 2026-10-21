import type { APIRequestContext } from '@playwright/test';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { BASE } from './env.js';

/** 直接调后端（走 dev server 的 /api 代理）：准备与清理测试数据用，不经过页面 */
const API = new URL('/api', BASE).toString();

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const AUTH_FILE = path.join(__dirname, '../../playwright/.auth/user.json');

/** setup 项目登录后存下的 token（v6 放在 localStorage 的 token 里） */
export function token(): string {
  const state = JSON.parse(fs.readFileSync(AUTH_FILE, 'utf8')) as {
    origins: { localStorage: { name: string; value: string }[] }[];
  };
  const t = state.origins.flatMap((o) => o.localStorage).find((i) => i.name === 'token')?.value;
  if (!t) throw new Error('playwright/.auth/user.json 里没有 token，setup 是否跑过？');
  return t;
}

/** `path` 从版本前缀写起，如 `/v3/discussions`、`/v2/sentence/{uid}` */
export async function call(request: APIRequestContext, method: string, path: string, data?: object) {
  const res = await request.fetch(`${API}${path}`, {
    method,
    data,
    headers: { Authorization: `Bearer ${token()}`, Accept: 'application/json' },
  });
  return { status: res.status(), body: await res.json().catch(() => null) };
}
