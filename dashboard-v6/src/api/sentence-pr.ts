import { get, post, put, delete_ } from "../request";
import type { ISentence } from "./sentence";
import type { IChannel } from "./Channel";
import type { IUser } from "./Auth";

// ─── 原有类型定义，保持不动 ─────────────────────────────────────────────────────

export interface ISentencePrRequest {
  book?: number;
  para?: number;
  begin?: number;
  end?: number;
  channel?: string;
  text: string;
}
export interface ISentencePrResponseData {
  book_id: number;
  paragraph: number;
  word_start: number;
  word_end: number;
  channel_uid: string;
}
export interface ISentencePrResponse {
  ok: boolean;
  message: string;
  data: {
    new: ISentencePrResponseData;
    count: number;
    webhook: { message: string; ok: boolean };
  };
}

// ─── 新增：PR 列表查询的响应类型 ────────────────────────────────────────────────

export interface ISentencePrData {
  id: string;
  uid: string;
  book: number;
  paragraph: number;
  word_start: number;
  word_end: number;
  content: string;
  html: string;
  editor: IUser;
  channel: IChannel;
  updated_at: string;
}

export interface ISentencePrListResponse {
  ok: boolean;
  message: string;
  data: { rows: ISentencePrData[]; count: number };
}

export interface IDeleteResponse {
  ok: boolean;
  message: string;
}

// ─── 新增：纯 HTTP 函数，供 hooks 层调用 ────────────────────────────────────────

/**
 * 查询某句下的 PR 列表
 */
export async function fetchSentencePrList(
  book: number,
  para: number,
  wordStart: number,
  wordEnd: number,
  channelId: string
): Promise<ISentencePrData[]> {
  const url = `/v2/sentpr?view=sent-info&book=${book}&para=${para}&start=${wordStart}&end=${wordEnd}&channel=${channelId}`;

  const json = await get<ISentencePrListResponse>(url);

  if (!json.ok) {
    throw new Error(json.message ?? "PR 列表加载失败");
  }

  return json.data.rows;
}

/**
 * 创建 PR（提交修改建议）
 */
export async function createSentencePr(
  sentence: ISentence,
  content: string
): Promise<void> {
  const json = await post<ISentencePrRequest, ISentencePrResponse>(
    `/v2/sentpr`,
    {
      book: sentence.book,
      para: sentence.para,
      begin: sentence.wordStart,
      end: sentence.wordEnd,
      channel: sentence.channel.id,
      text: content,
    }
  );

  if (!json.ok) {
    throw new Error(json.message ?? "提交建议失败");
  }
}

/**
 * 更新 PR 内容
 */
export async function updateSentencePr(
  prId: string,
  content: string
): Promise<void> {
  const json = await put<Pick<ISentencePrRequest, "text">, ISentencePrResponse>(
    `/v2/sentpr/${prId}`,
    { text: content }
  );

  if (!json.ok) {
    throw new Error(json.message ?? "更新建议失败");
  }
}

/**
 * 删除 PR
 */
export async function deleteSentencePr(prId: string): Promise<void> {
  const json = await delete_<IDeleteResponse>(`/v2/sentpr/${prId}`);

  if (!json.ok) {
    throw new Error(json.message ?? "删除失败");
  }
}
