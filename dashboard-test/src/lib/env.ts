/**
 * 测试配置的唯一出口。值来自环境变量，或 playwright.config.ts 加载的 `.env`
 * （模板见 `.env.example`）。spec 里不要再直接读 process.env、也不要写死账号密码。
 */
function required(name: string): string {
  const value = process.env[name];
  if (!value) {
    throw new Error(`缺少 ${name}：cp .env.example .env 后填写，或在环境变量里设置`);
  }
  return value;
}

export const BASE = process.env.E2E_BASE || 'http://127.0.0.1:4000/pcd-v2026/';
export const USER = required('E2E_USER');
export const PASS = required('E2E_PASS');
export const DISCUSSION_SENTENCE =
  process.env.E2E_DISCUSSION_SENTENCE || 'ee5259be-5d1e-4408-a2a6-566a5773eef1';
/**
 * 一个真实章节（book-起始段）与要看的版本：章节页讨论面板「本章」视图用。
 * 默认是本机开发库的数据，换环境用 E2E_CHAPTER / E2E_CHAPTER_CHANNEL 覆盖。
 */
export const CHAPTER = process.env.E2E_CHAPTER || '169-893';
export const CHAPTER_CHANNEL =
  process.env.E2E_CHAPTER_CHANNEL || '7fea264d-7a26-40f8-bef7-bc95102760fb';
/** 测试账号自己的一篇文章（文章底部问答区、讨论角标请求） */
export const ARTICLE = process.env.E2E_ARTICLE || 'f24cfd88-5e0c-486d-844b-8d237b5b80d7';
export const DESTRUCTIVE = process.env.E2E_DESTRUCTIVE !== '0';

/** 截图目录：E2E_SHOT 优先，否则 testlog/assets/<name>_latest */
export const shotDir = (name: string) => process.env.E2E_SHOT || `testlog/assets/${name}_latest`;
