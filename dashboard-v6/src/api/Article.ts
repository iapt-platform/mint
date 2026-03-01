import type { IStudio, IStudioApiResponse, IUser, TRole } from "./Auth";
import type { IChannel } from "./Channel";
import { get } from "../request";
import type { ITocPathNode } from "./pali-text";

export type TContentType = "text" | "markdown" | "html" | "json";

export type ArticleMode = "read" | "edit" | "wbw" | "auto";
export type ArticleType =
  | "anthology"
  | "article"
  | "series"
  | "chapter"
  | "para"
  | "cs-para"
  | "sent"
  | "sim"
  | "page"
  | "textbook"
  | "sent-original"
  | "sent-commentary"
  | "sent-nissaya"
  | "sent-translation"
  | "term"
  | "task";
/**
 * 每种article type 对应的路由参数
 * article/id?anthology=id&channel=id1,id2&mode=ArticleMode
 * chapter/book-para?channel=id1,id2&mode=ArticleMode
 * para/book?par=para1,para2&channel=id1,id2&mode=ArticleMode
 * cs-para/book-para?channel=id1,id2&mode=ArticleMode
 * sent/id?channel=id1,id2&mode=ArticleMode
 * sim/id?channel=id1,id2&mode=ArticleMode
 * textbook/articleId?course=id&mode=ArticleMode
 * exercise/articleId?course=id&exercise=id&username=name&mode=ArticleMode
 * exercise-list/articleId?course=id&exercise=id&mode=ArticleMode
 * sent-original/id
 */

export interface IArticleListApiResponse {
  article: string;
  title: string;
  level: string;
  children: number;
}
export interface IAnthologyDataRequest {
  title: string;
  subtitle: string;
  summary?: string;
  article_list?: IArticleListApiResponse[];
  lang: string;
  status: number;
  default_channel?: string | null;
}
export interface IAnthologyDataResponse {
  uid: string;
  title: string;
  subtitle: string;
  summary: string;
  article_list: IArticleListApiResponse[];
  studio: IStudio;
  default_channel?: IChannel;
  lang: string;
  status: number;
  childrenNumber: number;
  created_at: string;
  updated_at: string;
}
export interface IAnthologyResponse {
  ok: boolean;
  message: string;
  data: IAnthologyDataResponse;
}
export interface IAnthologyListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: IAnthologyDataResponse[];
    count: number;
  };
}

export interface IAnthologyStudioListApiResponse {
  ok: boolean;
  message: string;
  data: {
    count: number;
    rows: IAnthologyStudioListDataApiResponse[];
  };
}
export interface IAnthologyStudioListDataApiResponse {
  count: number;
  studio: IStudioApiResponse;
}

export interface IArticleDataRequest {
  uid: string;
  title: string;
  subtitle: string;
  summary?: string | null;
  content?: string;
  content_type?: string;
  status: number;
  lang: string;
  to_tpl?: boolean;
  anthology_id?: string;
}
export interface IChapterToc {
  key?: string;
  book: number;
  paragraph: number;
  level: number;
  pali_title: string /**巴利文标题 */;
  title?: string /**译文文标题 */;
  progress?: number[];
}
export interface IArticleDataResponse {
  uid: string;
  title: string;
  title_text?: string;
  subtitle: string;
  summary: string | null;
  _summary?: string;
  content?: string;
  content_type?: string;
  toc?: IChapterToc[];
  html?: string;
  path?: ITocPathNode[];
  status: number;
  lang: string;
  anthology_count?: number;
  anthology_first?: { uid: string; title: string };
  role?: TRole;
  studio?: IStudio;
  editor?: IUser;
  created_at: string;
  updated_at: string;
  from?: number;
  to?: number;
  mode?: string;
  paraId?: string;
  parent_uid?: string;
  channels?: string;
}
export interface IArticleResponse {
  ok: boolean;
  message: string;
  data: IArticleDataResponse;
}
export interface IArticleListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: IArticleDataResponse[];
    count: number;
  };
}

export interface IArticleCreateRequest {
  title: string;
  lang: string;
  studio: string;
  anthologyId?: string;
  parentId?: string;
  status?: number;
}

export interface IAnthologyCreateRequest {
  title: string;
  lang: string;
  studio: string;
}

export interface IArticleMapRequest {
  id?: string;
  collect_id?: string;
  collection?: { id: string; title: string };
  article_id?: string;
  level: number;
  title: string;
  title_text?: string;
  editor?: IUser;
  children?: number;
  status?: number;
  deleted_at?: string | null;
  created_at?: string;
  updated_at?: string;
}
export interface IArticleMapListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: IArticleMapRequest[];
    count: number;
  };
}
export interface IArticleMapAddRequest {
  anthology_id: string;
  article_id: string[];
  operation: string;
}
export interface IArticleMapUpdateRequest {
  data: IArticleMapRequest[];
  operation: string;
}
export interface IArticleMapAddResponse {
  ok: boolean;
  message: string;
  data: number;
}
export interface IDeleteResponse {
  ok: boolean;
  message: string;
  data: number;
}
export interface IArticleNavResponse {
  ok: boolean;
  data: IArticleNavData;
  message: string;
}

export interface IArticleNavData {
  curr?: IArticleMapRequest;
  prev?: IArticleMapRequest;
  next?: IArticleMapRequest;
}

export interface IPageNavResponse {
  ok: boolean;
  data: IPageNavData;
  message: string;
}

export interface IPageNavData {
  curr: IPageNavItem;
  prev: IPageNavItem;
  next: IPageNavItem;
}

export interface IPageNavItem {
  id: number;
  type: string;
  volume: number;
  page: number;
  book: number;
  paragraph: number;
  wid: number;
  pcd_book_id: number;
  created_at: string;
  updated_at: string;
}

export interface ICSParaNavResponse {
  ok: boolean;
  data: ICSParaNavData;
  message: string;
}

export interface ICSParaNavData {
  curr: ICSParaNavItem;
  prev?: ICSParaNavItem;
  next?: ICSParaNavItem;
  end: number;
}

export interface ICSParaNavItem {
  book: number;
  start: number;
  content: string;
}

export interface IArticleFtsListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: IArticleDataResponse[];
    page: { size: number; current: number; total: number };
  };
}

// src/api/Article.ts 新增部分

export const fetchChapterArticle = (
  articleId: string,
  mode: "read" | "edit",
  channelId?: string | null
): Promise<IArticleResponse> => {
  let url = `/api/v2/corpus-chapter/${articleId}?mode=${mode}`;
  if (channelId) url += `&channels=${channelId}`;
  return get<IArticleResponse>(url);
};

export const fetchParaArticle = (
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
  let url = `/api/v2/corpus-chapter/${paraId}?mode=${mode}&from=${from}&to=${to}`;
  if (channelId) url += `&channels=${channelId}`;
  return get<IArticleResponse>(url);
};
