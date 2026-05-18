// src/api/channel.ts
import type { LoaderFunctionArgs } from "react-router";
import type { IStudio, TRole } from "./Auth";
import { get, post } from "../request";
export interface IChannel {
  id: string;
  name: string;
  type?: TChannelType;
  lang?: string;
}
export type TChannelType =
  | "translation"
  | "nissaya"
  | "original"
  | "wbw"
  | "commentary"
  | "similar";
export interface IChannelApiData {
  id: string;
  name: string;
  type?: TChannelType;
}

export interface ChannelInfoProps {
  channel: IChannelApiData;
  studio: IStudio;
  count?: number;
}

export type TChannelSourceType = "original" | "reprint" | "ai";
export const SOURCE_TYPE_OPTIONS: TChannelSourceType[] = [
  "original",
  "reprint",
  "ai",
];
/**
 * 句子完成情况
 * [句子字符数，是否完成]
 *
 */
export type IFinal = [number, boolean];
export interface IApiResponseChannelData {
  uid: string;
  name: string;
  summary: string;
  type: TChannelType;
  studio: IStudio;
  lang: string;
  status: number;
  is_system: boolean;
  progress?: number;
  source_type?: TChannelSourceType | null;
  source_id?: string | null;
  created_at: string;
  updated_at: string;
  role?: TRole;
  final?: IFinal[];
  content_created_at: string;
  content_updated_at: string;
}
export interface IApiResponseChannel {
  ok: boolean;
  message: string;
  data: IApiResponseChannelData;
}
export interface IApiResponseChannelList {
  ok: boolean;
  message: string;
  data: {
    rows: IApiResponseChannelData[];
    count: number;
  };
}

export interface ISentInChapterListResponse {
  ok: boolean;
  data: ISentInChapterListData;
  message: string;
}

export interface ISentInChapterListData {
  rows: ISentInChapterListDataRow[];
  count: number;
}

export interface ISentInChapterListDataRow {
  book: number;
  paragraph: number;
  word_begin: number;
  word_end: number;
}

export interface IResNumberResponse {
  ok: boolean;
  message: string;
  data: {
    my: number;
    collaboration: number;
  };
}

export interface IResNumber {
  translation?: number;
  nissaya?: number;
  commentary?: number;
  origin?: number;
  sim?: number;
}

export interface IProgressRequest {
  sentence: string[];
  owner?: string;
}

export interface IChannelItem {
  id: number;
  uid: string;
  title: string;
  summary: string;
  type: TChannelType;
  studio: IStudio;
  shareType: string;
  role?: string;
  publicity: number;
  final?: IFinal[];
  progress: number;
  createdAt: number;
  content_created_at?: string;
  content_updated_at?: string;
}

export async function channelLoader({ params }: LoaderFunctionArgs) {
  const channelId = params.channelId;

  if (!channelId) {
    throw new Response("Missing channelId", { status: 400 });
  }

  const res = await get<IApiResponseChannel>(`/api/v2/channel/${channelId}`);

  if (!res.ok) {
    throw new Response("Channel not found", { status: 404 });
  }

  return res.data;
}

// ─── 获取章节内所有句子 ID ───────────────────────────────────────────────────

/**
 * 按 book + paragraph 拉取章节内所有句子坐标
 */
export const fetchSentencesInChapter = (
  bookId: string,
  para: string
): Promise<ISentInChapterListResponse> =>
  get<ISentInChapterListResponse>(
    `/api/v2/sentences-in-chapter?book=${bookId}&para=${para}`
  );

// ─── 获取频道进度列表 ────────────────────────────────────────────────────────

/**
 * 批量查询句子在各频道的翻译进度
 *
 * @param sentences 句子 ID 列表，格式 `book-para-wordBegin-wordEnd`
 * @param owner     "all" = 全部可见频道；"my" = 仅自己拥有的
 */
export const fetchChannelProgress = (
  sentences: string[],
  owner: "all" | "my" = "all"
): Promise<IApiResponseChannelList> =>
  post<IProgressRequest, IApiResponseChannelList>("/api/v2/channel-progress", {
    sentence: sentences,
    owner,
  });
