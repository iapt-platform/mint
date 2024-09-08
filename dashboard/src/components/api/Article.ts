import { IStudio } from "../auth/Studio";
import { IUser } from "../auth/User";
import { IChannel } from "../channel/Channel";
import { ITocPathNode } from "../corpus/TocPath";
import type { IStudioApiResponse, TRole } from "./Auth";

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
