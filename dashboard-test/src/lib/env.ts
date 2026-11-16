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

/**
 * 课程测试（course.spec）的第二、第三个账号：学生与助教。可选——没配就跳过多角色用例。
 * 三个账号必须互不相同；E2E_USER 充当课程的拥有者（老师）。
 */
export const STUDENT_USER = process.env.E2E_STUDENT_USER || '';
export const STUDENT_PASS = process.env.E2E_STUDENT_PASS || '';
export const ASSISTANT_USER = process.env.E2E_ASSISTANT_USER || '';
export const ASSISTANT_PASS = process.env.E2E_ASSISTANT_PASS || '';
export const HAS_STUDENT = !!(STUDENT_USER && STUDENT_PASS);
export const HAS_ASSISTANT = !!(ASSISTANT_USER && ASSISTANT_PASS);

/**
 * 课程用的教材与标准答案。默认是本机开发库里的「初级班课本范例」文集（公开），
 * 其中「练习一」一篇只有一个句子 168-916-2-9；标准答案 channel 在这一段上有逐词解析与译文。
 * 换环境时四项一起覆盖。
 */
export const COURSE_TEXTBOOK = process.env.E2E_COURSE_TEXTBOOK || '7be1505b-c439-475f-996c-6e0b4253eae5';
export const COURSE_ARTICLE = process.env.E2E_COURSE_ARTICLE || 'cc82c177-e538-4c23-b142-7b001a435db2';
export const COURSE_ANSWER_CHANNEL =
  process.env.E2E_COURSE_ANSWER_CHANNEL || '7fea264d-7a26-40f8-bef7-bc95102760fb';
/** 学生做逐词作业时点的那个词（必须在 COURSE_ARTICLE 的句子里） */
export const COURSE_WORD = process.env.E2E_COURSE_WORD || 'viharati';

/**
 * Laravel 端 library 页面根地址（/library/course 在这里，不在 dashboard-v6 的 dev server 上）。
 * 本机 dev：http://127.0.0.1:8000/library/；线上 / staging 与 dashboard 同域，默认按 E2E_BASE 推出来。
 */
export const LIBRARY = process.env.E2E_LIBRARY || new URL('/library/', BASE).toString();

/** 截图目录：E2E_SHOT 优先，否则 testlog/assets/<name>_latest */
export const shotDir = (name: string) => process.env.E2E_SHOT || `testlog/assets/${name}_latest`;
