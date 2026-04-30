import type { ArticleMode, TContentType } from "./article";

import { get, put } from "../request";

import type { IStudio, IUser } from "./Auth";
import type { IChannel } from "./channel";
import type { ISuggestionCount } from "./Suggestion";
import type { ITocPathNode } from "./pali-text";

// ─── 以下是原有类型定义，保持不动 ─────────────────────────────────────────────
export interface ISentence {
  id?: string;
  uid?: string;
  content: string | null;
  contentType?: TContentType;
  html: string;
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
  editor: IUser;
  acceptor?: IUser;
  prEditAt?: string;
  channel: IChannel;
  studio?: IStudio;
  forkAt?: string | null;
  updateAt: string;
  createdAt?: string;
  suggestionCount?: ISuggestionCount;
  openInEditMode?: boolean;
  translationChannels?: string[];
}

export interface ISentenceDiffRequest {
  sentences: string[];
  channels: string[];
}
export interface ISentenceDiffData {
  book_id: number;
  paragraph: number;
  word_start: number;
  word_end: number;
  channel_uid: string;
  content: string | null;
  content_type: string;
  editor_uid: string;
  updated_at: string;
}
export interface ISentenceDiffResponse {
  ok: boolean;
  message: string;
  data: { rows: ISentenceDiffData[]; count: number };
}

export interface ISentenceRequest {
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
  channel: string;
  content: string | null;
  contentType?: TContentType;
  prEditor?: string;
  prId?: string;
  prUuid?: string;
  prEditAt?: string;
  channels?: string;
  html?: boolean;
  token?: string | null;
}

export interface ISentenceData {
  id?: string;
  book: number;
  paragraph: number;
  word_start: number;
  word_end: number;
  content: string;
  content_type?: TContentType;
  html: string;
  editor: IUser;
  channel: IChannel;
  studio: IStudio;
  updated_at: string;
  acceptor?: IUser;
  pr_edit_at?: string;
  fork_at?: string;
  suggestionCount?: ISuggestionCount;
}

export interface ISentenceResponse {
  ok: boolean;
  message: string;
  data: ISentenceData;
}
export interface ISentenceListResponse {
  ok: boolean;
  message: string;
  data: { rows: ISentenceData[]; count: number };
}
export interface ISentenceNewRequest {
  sentences: ISentenceDiffData[];
  channel?: string;
  copy?: boolean;
  fork_from?: string;
}

export interface ISentenceWbwListResponse {
  ok: boolean;
  message: string;
  data: { rows: ISentEditData[]; count: number };
}

export interface IEditableSentence {
  ok: boolean;
  message: string;
  data: ISentEditData;
}

export interface ISentEditData {
  id: string;
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
  channels?: string[];
  origin?: ISentence[];
  translation?: ISentence[];
  commentaries?: ISentence[];
  answer?: ISentence;
  path?: ITocPathNode[];
  layout?: "row" | "column";
  tranNum?: number;
  nissayaNum?: number;
  commNum?: number;
  originNum: number;
  simNum?: number;
  compact?: boolean;
  mode?: ArticleMode;
  showWbwProgress?: boolean;
  readonly?: boolean;
  wbwProgress?: number;
  wbwScore?: number;
}

// ─── 原有函数，保持不动，重构完成后再删除 ──────────────────────────────────────

export const sentSave = async (
  sent: ISentence
): Promise<ISentenceResponse | null> => {
  //FIXME
  //store.dispatch(statusChange({ status: "loading" }));
  const id = `${sent.book}_${sent.para}_${sent.wordStart}_${sent.wordEnd}_${sent.channel.id}`;
  const url = `/api/v2/sentence/${id}?mode=edit&html=true`;
  console.info("SentWbwEdit url", url);

  try {
    const res = await put<ISentenceRequest, ISentenceResponse>(url, {
      book: sent.book,
      para: sent.para,
      wordStart: sent.wordStart,
      wordEnd: sent.wordEnd,
      channel: sent.channel.id,
      content: sent.content,
      contentType: sent.contentType,
      channels: sent.translationChannels?.join(),
      token: sessionStorage.getItem(sent.channel.id),
    });
    return res;
  } catch (e) {
    console.error("catch", e);
    return null;
  }
};

// ─── 新增：纯 HTTP 函数，供 hooks 层调用 ────────────────────────────────────────
// 规范：只管收发，不含 UI 反馈、不含 store.dispatch、不含业务判断

/**
 * 加载单条句子
 */
export async function fetchSentence(
  book: number,
  para: number,
  wordStart: number,
  wordEnd: number,
  channelId: string
): Promise<ISentenceData> {
  const sentId = `${book}-${para}-${wordStart}-${wordEnd}`;
  const url = `/api/v2/sentence?view=channel&sentence=${sentId}&channel=${channelId}&html=true`;

  const json = await get<ISentenceListResponse>(url);

  if (!json.ok || json.data.count === 0) {
    throw new Error(json.message ?? "句子加载失败");
  }

  return json.data.rows[0];
}

/**
 * 保存句子内容（新建 or 更新）
 */
export async function saveSentence(sent: ISentence): Promise<ISentenceData> {
  const id = `${sent.book}_${sent.para}_${sent.wordStart}_${sent.wordEnd}_${sent.channel.id}`;
  const url = `/api/v2/sentence/${id}?mode=edit&html=true`;

  const json = await put<ISentenceRequest, ISentenceResponse>(url, {
    book: sent.book,
    para: sent.para,
    wordStart: sent.wordStart,
    wordEnd: sent.wordEnd,
    channel: sent.channel.id,
    content: sent.content,
    contentType: sent.contentType,
    channels: sent.translationChannels?.join(),
    token: sessionStorage.getItem(sent.channel.id),
  });

  if (!json.ok) {
    throw new Error(json.message ?? "保存失败");
  }

  return json.data;
}

/**
 * 采纳 PR：把 PR 内容写回正式句子
 */
export async function acceptSentencePr(
  prData: ISentence
): Promise<ISentenceData> {
  const id = `${prData.book}_${prData.para}_${prData.wordStart}_${prData.wordEnd}_${prData.channel.id}`;
  const url = `/api/v2/sentence/${id}?mode=edit&html=true`;

  const json = await put<ISentenceRequest, ISentenceResponse>(url, {
    book: prData.book,
    para: prData.para,
    wordStart: prData.wordStart,
    wordEnd: prData.wordEnd,
    channel: prData.channel.id,
    content: prData.content,
    prEditor: prData.editor?.id,
    prId: prData.id,
    prUuid: prData.uid,
    prEditAt: prData.updateAt,
    token: sessionStorage.getItem(prData.channel.id),
  });

  if (!json.ok) {
    throw new Error(json.message ?? "采纳失败");
  }

  return json.data;
}

/**
 * 获取 Snowflake ID（wbw 节点赋 uid 用）
 */
export async function fetchSnowflakeIds(count: number): Promise<string[]> {
  const json = await get<{
    ok: boolean;
    message?: string;
    data: { rows: string[]; count: number };
  }>(`/api/v2/snowflake?count=${count}`);

  if (!json.ok) {
    throw new Error(json.message ?? "获取 ID 失败");
  }

  return json.data.rows;
}
