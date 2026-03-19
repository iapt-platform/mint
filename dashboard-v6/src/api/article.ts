//src/api/article.ts

import type { IStudio, IStudioApiResponse, IUser, TRole } from "./Auth";
import type { IChannel } from "./channel";
import { delete_, get, post, put } from "../request";
import type { ITocPathNode } from "./pali-text";
import type { LoaderFunctionArgs } from "react-router";
import type { ListNodeData } from "../components/article/components/EditableTree";

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

export interface IAnthologyData {
  id: string;
  title: string;
  subTitle: string;
  summary: string;
  articles: ListNodeData[];
  studio: IStudio;
  created_at: string;
  updated_at: string;
}

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
  content_type?: TContentType;
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

// ─────────────────────────────────────────────
// Query param types
// ─────────────────────────────────────────────

export interface IFetchArticleParams {
  /** 频道 ID 列表，后端用 `_` 分隔；anthology 有 default_channel 时可不传 */
  channelIds?: string[];
  /** 文集 UUID，影响 path / toc 生成和 channel 回退逻辑 */
  anthologyId?: string | null;
  /** 课程 ID，影响 channel 选择（答案频道 / 用户作业频道） */
  courseId?: string;
  /** 读写模式，后端默认 read */
  mode?: ArticleMode;
  /** 渲染格式，后端默认 react */
  format?: "react" | "text" | "markdown" | "html";
  /** 是否显示原文，后端默认 true */
  origin?: boolean;
  /** 是否显示段落编号，后端默认 false */
  paragraph?: boolean;
}

// ─────────────────────────────────────────────
// Article CRUD
// ─────────────────────────────────────────────

/**
 * 将 IFetchArticleParams 序列化为 query string（不含 ? 前缀）
 *
 * 与后端默认值一致的参数不附加，保持 URL 简洁：
 *   mode      默认 read
 *   format    默认 react
 *   origin    默认 true
 *   paragraph 默认 false
 */
const buildArticleQuery = (params: IFetchArticleParams): string => {
  const { channelIds, anthologyId, courseId, mode, format, origin, paragraph } =
    params;

  const parts: string[] = [];

  if (mode && mode !== "read") parts.push(`mode=${mode}`);
  if (format && format !== "react") parts.push(`format=${format}`);
  if (origin === false) parts.push(`origin=false`);
  if (paragraph === true) parts.push(`paragraph=true`);
  if (channelIds && channelIds.length > 0)
    parts.push(`channel=${channelIds.join("_")}`);
  if (anthologyId) parts.push(`anthology=${anthologyId}`);
  if (courseId) parts.push(`course=${courseId}`);

  return parts.join("&");
};

/**
 * 获取单篇文章
 *
 * 合并了原 fetchArticle / fetchArticleOriginText / fetchParentArticle，
 * 通过 params 区分场景：
 *
 * ```ts
 * // 普通阅读（无参）
 * fetchArticle(id)
 *
 * // 取父节点（原 fetchParentArticle）
 * fetchArticle(parentId)
 *
 * // 带文集上下文，返回 path / toc
 * fetchArticle(id, { anthologyId })
 *
 * // 带频道
 * fetchArticle(id, { channelIds: ['ch1', 'ch2'] })
 *
 * // 取原文纯文本（原 fetchArticleOriginText）
 * fetchArticle(id, { format: 'text' })   // origin 后端默认 true，无需显式传
 *
 * // 编辑模式
 * fetchArticle(id, { mode: 'edit', anthologyId })
 *
 * // 课程场景
 * fetchArticle(id, { courseId, channelIds })
 * ```
 *
 * GET /v2/article/:articleId?[mode=]&[format=]&[origin=]&[paragraph=]
 *                             &[channel=]&[anthology=]&[course=]
 */
export const fetchArticle = (
  articleId: string,
  params: IFetchArticleParams = {}
): Promise<IArticleResponse> => {
  const query = buildArticleQuery(params);
  const url = `/api/v2/article/${articleId}${query ? `?${query}` : ""}`;
  return get<IArticleResponse>(url);
};

/**
 * 创建文章
 *
 * POST /v2/article
 */
export const createArticle = (
  data: IArticleCreateRequest
): Promise<IArticleResponse> => {
  return post<IArticleCreateRequest, IArticleResponse>(`/api/v2/article`, data);
};

/**
 * 更新文章
 *
 * PUT /v2/article/:articleId
 */
export const updateArticle = (
  articleId: string,
  data: IArticleDataRequest
): Promise<IArticleResponse> => {
  console.debug("updateArticle", articleId);
  return put<IArticleDataRequest, IArticleResponse>(
    `/api/v2/article/${articleId}`,
    data
  );
};

/**
 * 删除文章
 *
 * DELETE /v2/article/:id
 */
export const deleteArticle = (id: string): Promise<IDeleteResponse> => {
  return delete_<IDeleteResponse>(`/api/v2/article/${id}`);
};

// ─────────────────────────────────────────────
// Article list（Studio 管理视图）
// ─────────────────────────────────────────────
import type { SortOrder } from "antd/es/table/interface";

/** 排序字段，对应后端 order 参数 */
export type TArticleSortField = "updated_at" | "created_at" | "title";

/** view=template：按 studio_name 获取模板文章 */
interface IListArticleTemplateParams {
  view: "template";
  studioName: string;
}

/** view=studio 时 anthology 的过滤选项
 *  - 不传        不按文集过滤
 *  - 'all'       全部（含已归集和未归集）
 *  - 'none'      未归入任何我的文集的文章
 *  - UUID string 指定文集内的文章
 */
type TAnthologyFilter = "all" | "none" | string;

/** view=studio：当前用户 studio 下的文章（支持协作、文集过滤、分页搜索） */
interface IListArticleStudioParams {
  view: "studio";
  /** studio 名称，对应后端 name 参数 */
  studioName: string;
  /** 'my'（默认）= 自己的文章；'collab' = 协作文章 */
  view2?: "my" | "collab";
  /** 文集过滤，不传则不过滤 */
  anthology?: TAnthologyFilter;
}

/** view=public：公开文章列表 */
interface IListArticlePublicParams {
  view: "public";
}

/** 所有 view 共享的分页 / 搜索 / 排序参数 */
interface IListArticleCommonParams {
  current?: number;
  pageSize?: number;
  keyword?: string;
  /** subtitle 精确匹配 */
  subtitle?: string;
  /** 是否同时返回 content 字段，对应后端 content=true */
  withContent?: boolean;
  /** 排序字段 */
  orderBy?: TArticleSortField;
  /** 排序方向，对应 antd SortOrder */
  sortOrder?: SortOrder;
}

export type IListArticleParams = IListArticleCommonParams &
  (
    | IListArticleTemplateParams
    | IListArticleStudioParams
    | IListArticlePublicParams
  );

/**
 * 获取文章列表
 *
 * GET /v2/article?view=template|studio|public
 *                &[studio_name=|name=]
 *                &[view2=my|collab]
 *                &[anthology=all|none|<uuid>]
 *                &[search=]&[subtitle=]&[content=true]
 *                &limit=&offset=
 *                &[order=]&[dir=]
 */
export const fetchArticleList = (
  params: IListArticleParams
): Promise<IArticleListResponse> => {
  const {
    current = 1,
    pageSize = 20,
    keyword,
    subtitle,
    withContent,
    orderBy,
    sortOrder,
  } = params;

  const offset = (current - 1) * pageSize;
  const parts: string[] = [`view=${params.view}`];

  // view 专属参数
  if (params.view === "template") {
    parts.push(`studio_name=${params.studioName}`);
  } else if (params.view === "studio") {
    parts.push(`name=${params.studioName}`);
    if (params.view2 && params.view2 !== "my")
      parts.push(`view2=${params.view2}`);
    if (params.anthology !== undefined)
      parts.push(`anthology=${params.anthology}`);
  }

  // 公共参数
  parts.push(`limit=${pageSize}`, `offset=${offset}`);
  if (keyword) parts.push(`search=${keyword}`);
  if (subtitle) parts.push(`subtitle=${subtitle}`);
  if (withContent) parts.push(`content=true`);

  // 排序：SortOrder → 后端 order/dir 参数
  if (orderBy && sortOrder) {
    const dir = sortOrder === "ascend" ? "asc" : "desc";
    parts.push(`order=${orderBy}`, `dir=${dir}`);
  }

  const url = `/api/v2/article?${parts.join("&")}`;
  return get<IArticleListResponse>(url);
};

// src/api/Article.ts 新增部分

export const fetchAnthology = (id: string): Promise<IAnthologyResponse> => {
  return get<IAnthologyResponse>(`/api/v2/anthology/${id}`);
};

export async function anthologyLoader({ params }: LoaderFunctionArgs) {
  const id = params.anthologyId;

  if (!id) {
    throw new Response("Missing anthologyId", { status: 400 });
  }

  const res = await fetchAnthology(id);

  if (!res.ok) {
    throw new Response("anthology not found", { status: 404 });
  }

  return res.data;
}

export async function articleLoader({ params }: LoaderFunctionArgs) {
  const id = params.articleId;

  if (!id) {
    throw new Response("Missing articleId", { status: 400 });
  }

  const res = await fetchArticle(id);

  if (!res.ok) {
    throw new Response("article not found", { status: 404 });
  }

  return res.data;
}
