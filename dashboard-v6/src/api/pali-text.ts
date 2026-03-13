// src/api/pali-text.ts
import type { MenuProps } from "antd";
import { get } from "../request";
import type { ArticleMode, IArticleResponse } from "./article";
import type { IWidgetSentEditInner } from "../components/sentence/SentEdit";
import type { PaginatedResponse } from ".";

export interface ITocPathNode {
  key?: string;
  book?: number;
  paragraph?: number;
  title: string;
  paliTitle?: string;
  level: number;
  menu?: MenuProps["items"];
}

export interface IApiPaliChapterList {
  id: string;
  book: number;
  paragraph: number;
  level: number;
  toc: string;
  title: string;
  lenght: number;
  chapter_len: number;
  next_chapter: number;
  prev_chapter: number;
  parent: number;
  chapter_strlen: number;
  path: string;
  progress_line?: number[];
}
export interface IPaliChapterListResponse {
  ok: boolean;
  message: string;
  data: { rows: IApiPaliChapterList[]; count: number };
}
export interface IApiResponsePaliChapter {
  ok: boolean;
  message: string;
  data: IApiPaliChapterList;
}

//////////////////

export interface IPaliPara {
  book: number;
  paragraph: number;
  level: number;
  class: string;
  toc: string;
  text: string;
  html: string;
  lenght: number;
  chapter_len: number;
  next_chapter: number;
  prev_chapter: number;
  parent: number;
  chapter_strlen: number;
  path: string;
  uid: string;
}

export interface IPaliParagraphResponse {
  ok: boolean;
  message: string;
  data: IPaliPara;
}
export interface IPaliListResponse {
  ok: boolean;
  message: string;
  data: { rows: IPaliPara[]; count: number };
}

//

export interface IPaliToc {
  book: number;
  paragraph: number;
  level: string;
  toc: string;
  translation?: string;
}

export interface IPaliTocListResponse {
  ok: boolean;
  message: string;
  data: { rows: IPaliToc[]; count: number };
}

//

export interface IChapterToc {
  book: number;
  paragraph: number;
  level: number;
  text: string | null;
  chapter_len: number;
  chapter_strlen: number;
  parent: number;
}

export interface IChapterTocListResponse {
  ok: boolean;
  message: string;
  data: { rows: IChapterToc[]; count: number };
}

export interface IFetchPaliBookTocParams {
  /** 二选一：传 series 走系列模式，否则传 book + para */
  series?: string;
  book?: number;
  para?: number;
}

/**
 * 获取巴利文目录列表
 * GET /api/v2/palitext?view=book-toc[&series=xxx | &book=x&para=y]
 */
export const fetchPaliBookToc = (
  params: IFetchPaliBookTocParams
): Promise<IPaliTocListResponse> => {
  const query = new URLSearchParams({ view: "book-toc" });

  if (params.series) {
    query.set("series", params.series);
  } else {
    if (params.book !== undefined) query.set("book", String(params.book));
    if (params.para !== undefined) query.set("para", String(params.para));
  }

  return get<IPaliTocListResponse>(`/api/v2/palitext?${query.toString()}`);
};

export const fetchChapter = (
  articleId: string,
  mode: "read" | "edit",
  channelId?: string | null
): Promise<IArticleResponse> => {
  let url = `/api/v2/chapter-content/${articleId}?mode=${mode}`;
  if (channelId) url += `&channels=${channelId}`;
  return get<IArticleResponse>(url);
};

export const fetchPara = (
  book: string,
  para: string,
  mode: "read" | "edit",
  channelId?: string | null
): Promise<IArticleResponse> => {
  let url = `/api/v2/corpus?view=para&book=${book}&par=${para}&mode=${mode}`;
  if (channelId) url += `&channels=${channelId}`;
  return get<IArticleResponse>(url);
};

export const fetchNextParaChunk = (
  paraId: string,
  mode: string,
  from: number,
  to: number,
  channelId?: string | null
): Promise<IArticleResponse> => {
  let url = `/api/v2/chapter-content/${paraId}?mode=${mode}&from=${from}&to=${to}`;
  if (channelId) url += `&channels=${channelId}`;
  return get<IArticleResponse>(url);
};

export interface IParagraphNode {
  book: number;
  para: number;
  mode?: ArticleMode;
  channels?: string[];
  sentenceIds: string[];
  children?: IWidgetSentEditInner[];
}

type ParagraphNodeListResponse = PaginatedResponse<IParagraphNode>;

export const fetchParaNodeChunk = (
  book: number,
  from: number,
  to: number,
  mode: ArticleMode,
  channelIds?: string | null
): Promise<ParagraphNodeListResponse> => {
  let url = `/api/v2/paragraph-content?book=${book}&mode=${mode}&para=${from}&to=${to}`;
  if (channelIds) url += `&channels=${channelIds}`;
  return get<ParagraphNodeListResponse>(url);
};
