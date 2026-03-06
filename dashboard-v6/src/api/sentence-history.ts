import { get } from "../request";
import type { IStudio, IUser } from "./Auth";
import type { IChannel } from "./channel";

// ─── 原有类型定义，保持不动 ─────────────────────────────────────────────────────

export interface ISentHistoryData {
  id: string;
  sent_uid: string;
  content: string;
  editor: IUser;
  landmark: string;
  fork_from?: IChannel;
  fork_studio?: IStudio;
  pr_from?: string | null;
  accepter?: IUser;
  created_at: string;
}

export interface ISentHistoryListResponse {
  ok: boolean;
  message: string;
  data: { rows: ISentHistoryData[]; count: number };
}

export interface ISentHistory {
  content: string;
  editor: IUser;
  fork_from?: IChannel;
  pr_from?: string | null;
  accepter?: IUser;
  createdAt: string;
}

// ─── 新增：纯 HTTP 函数，供 hooks 层调用 ────────────────────────────────────────
// history 是只读表，只有查询操作

export type THistoryView = "sentence" | "channel";

/**
 * 查询某句的修改历史
 * @param sentId   句子的数据库 id（sentences.id）
 * @param view     查询视角
 * @param fork     是否只看 fork 记录（EditInfo 里的 Fork 组件用）
 */
export async function fetchSentenceHistory(
  sentId: string,
  view: THistoryView = "sentence",
  fork = false
): Promise<ISentHistoryData[]> {
  let url = `/v2/sent_history?view=${view}&id=${sentId}`;
  if (fork) {
    url += `&fork=1`;
  }

  const json = await get<ISentHistoryListResponse>(url);

  if (!json.ok) {
    throw new Error(json.message ?? "历史记录加载失败");
  }

  return json.data.rows;
}
